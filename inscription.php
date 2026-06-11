<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('formations.php');
}

$slotId = (int) ($_POST['slot_id'] ?? 0);

$stmt = db()->prepare("
    SELECT ts.*, COUNT(sr.id) AS registered
    FROM training_slots ts
    LEFT JOIN slot_registrations sr ON sr.slot_id = ts.id AND sr.status = 'registered'
    WHERE ts.id = ?
    GROUP BY ts.id
");
$stmt->execute([$slotId]);
$slot = $stmt->fetch();

if (!$slot) {
    flash('Créneau introuvable.', 'error');
    redirect('formations.php');
}

$stmt = db()->prepare('SELECT * FROM slot_registrations WHERE slot_id = ? AND user_id = ?');
$stmt->execute([$slotId, $user['id']]);
$registration = $stmt->fetch();

if ($registration && $registration['status'] === 'registered') {
    $stmt = db()->prepare('UPDATE slot_registrations SET status = "cancelled" WHERE id = ?');
    $stmt->execute([$registration['id']]);
    flash('Inscription annulée.');
} else {
    if ((int) $slot['registered'] >= (int) $slot['capacity']) {
        flash('Ce créneau est complet.', 'error');
        redirect('formation_view.php?id=' . $slotId);
    }

    if ($registration) {
        $stmt = db()->prepare('UPDATE slot_registrations SET status = "registered", created_at = NOW() WHERE id = ?');
        $stmt->execute([$registration['id']]);
    } else {
        $stmt = db()->prepare('INSERT INTO slot_registrations (slot_id, user_id) VALUES (?, ?)');
        $stmt->execute([$slotId, $user['id']]);
    }

    flash('Inscription enregistrée.');
}

redirect('formation_view.php?id=' . $slotId);
