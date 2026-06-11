<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('formations.php');
}

$slotId = (int) ($_POST['slot_id'] ?? 0);
$email = mb_strtolower(trim($_POST['participant_email'] ?? ''));

if ($slotId < 1) {
    flash('Créneau introuvable.', 'error');
    redirect('formations.php');
}

if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    flash('Le formulaire a expiré. Merci de réessayer.', 'error');
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
        flash('Ce créneau est déjà passé.', 'error');
        redirect('formation_view.php?id=' . $slotId);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM slot_registrations WHERE slot_id = ? AND status = 'registered'");
    $stmt->execute([$slotId]);

    if ((int) $stmt->fetchColumn() < (int) $slot['capacity']) {
        $pdo->rollBack();
        flash('Des places sont encore disponibles : vous pouvez vous inscrire directement.', 'error');
        redirect('formation_view.php?id=' . $slotId);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM slot_registrations WHERE slot_id = ? AND participant_email = ? AND status = 'registered'");
    $stmt->execute([$slotId, $email]);

    if ((int) $stmt->fetchColumn() > 0) {
        $pdo->rollBack();
        flash('Cette adresse e-mail est déjà inscrite à cette formation.', 'error');
        redirect('formation_view.php?id=' . $slotId);
    }

    $stmt = $pdo->prepare('INSERT IGNORE INTO slot_interests (slot_id, participant_email) VALUES (?, ?)');
    $stmt->execute([$slotId, $email]);
    $created = $stmt->rowCount() > 0;

    $pdo->commit();
    flash($created ? 'Votre intérêt pour cette formation a bien été enregistré.' : 'Votre intérêt pour cette formation était déjà enregistré.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    flash('Votre intérêt n’a pas pu être enregistré. Merci de réessayer.', 'error');
}

redirect('formation_view.php?id=' . $slotId);
