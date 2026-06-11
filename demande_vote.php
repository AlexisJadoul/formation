<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('demandes.php');
}

$requestId = (int) ($_POST['request_id'] ?? 0);

$stmt = db()->prepare("SELECT * FROM training_requests WHERE id = ? AND status = 'approved'");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request || (int) $request['user_id'] === (int) $user['id']) {
    flash('Action impossible sur cette demande.', 'error');
    redirect('demandes.php');
}

$stmt = db()->prepare('SELECT id FROM request_votes WHERE request_id = ? AND user_id = ?');
$stmt->execute([$requestId, $user['id']]);
$vote = $stmt->fetch();

if ($vote) {
    $stmt = db()->prepare('DELETE FROM request_votes WHERE id = ?');
    $stmt->execute([$vote['id']]);
    flash('Ton intérêt a été retiré.');
} else {
    $stmt = db()->prepare('INSERT INTO request_votes (request_id, user_id) VALUES (?, ?)');
    $stmt->execute([$requestId, $user['id']]);
    flash('Ton intérêt a été pris en compte.');
}

redirect('demandes.php');
