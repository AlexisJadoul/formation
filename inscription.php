<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('formations.php');
}

$slotId = (int) ($_POST['slot_id'] ?? 0);
$name = trim($_POST['participant_name'] ?? '');
$email = mb_strtolower(trim($_POST['participant_email'] ?? ''));

if ($slotId < 1) {
    flash('Créneau introuvable.', 'error');
    redirect('formations.php');
}

if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    flash('Le formulaire a expiré. Merci de réessayer.', 'error');
    redirect('formation_view.php?id=' . $slotId);
}

if ($name === '' || $email === '') {
    flash('Votre nom et votre adresse e-mail sont obligatoires.', 'error');
    redirect('formation_view.php?id=' . $slotId);
}

if (mb_strlen($name) > 150) {
    flash('Votre nom ne peut pas dépasser 150 caractères.', 'error');
    redirect('formation_view.php?id=' . $slotId);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
    flash('Adresse e-mail invalide.', 'error');
    redirect('formation_view.php?id=' . $slotId);
}

$pdo = db();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id, capacity, start_at FROM training_slots WHERE id = ? FOR UPDATE');
    $stmt->execute([$slotId]);
    $slot = $stmt->fetch();

    if (!$slot) {
        $pdo->rollBack();
        flash('Créneau introuvable.', 'error');
        redirect('formations.php');
    }

    if (strtotime($slot['start_at']) <= time()) {
        $pdo->rollBack();
        flash('Les inscriptions à ce créneau sont closes.', 'error');
        redirect('formation_view.php?id=' . $slotId);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM slot_registrations WHERE slot_id = ? AND status = 'registered'");
    $stmt->execute([$slotId]);

    if ((int) $stmt->fetchColumn() >= (int) $slot['capacity']) {
        $pdo->rollBack();
        flash('Ce créneau est complet.', 'error');
        redirect('formation_view.php?id=' . $slotId);
    }

    $stmt = $pdo->prepare('SELECT id, status FROM slot_registrations WHERE slot_id = ? AND participant_email = ?');
    $stmt->execute([$slotId, $email]);
    $registration = $stmt->fetch();

    if ($registration && $registration['status'] === 'registered') {
        $pdo->rollBack();
        flash('Cette adresse e-mail est déjà inscrite à ce créneau.', 'error');
        redirect('formation_view.php?id=' . $slotId);
    }

    if ($registration) {
        $stmt = $pdo->prepare("
            UPDATE slot_registrations
            SET participant_name = ?, status = 'registered', created_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$name, $registration['id']]);
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO slot_registrations (slot_id, participant_name, participant_email)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([$slotId, $name, $email]);
    }

    $pdo->commit();
    flash('Votre inscription à cette formation est confirmée, ' . $name . '.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    flash('L’inscription n’a pas pu être enregistrée. Merci de réessayer.', 'error');
}

redirect('formation_view.php?id=' . $slotId);
