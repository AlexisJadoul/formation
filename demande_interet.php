<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('demandes.php');
}

$requestId = (int) ($_POST['request_id'] ?? 0);
$email = mb_strtolower(trim($_POST['participant_email'] ?? ''));

if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    flash('Le formulaire a expiré. Merci de réessayer.', 'error');
    redirect('demandes.php');
}

if ($requestId < 1 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
    flash('Adresse e-mail ou demande invalide.', 'error');
    redirect('demandes.php');
}

try {
    $stmt = db()->prepare("SELECT id FROM training_requests WHERE id = ? AND status = 'approved'");
    $stmt->execute([$requestId]);

    if (!$stmt->fetch()) {
        flash('Cette demande n’est pas disponible.', 'error');
        redirect('demandes.php');
    }

    $stmt = db()->prepare('INSERT IGNORE INTO request_interests (request_id, participant_email) VALUES (?, ?)');
    $stmt->execute([$requestId, $email]);
    flash($stmt->rowCount() > 0 ? 'Votre intérêt a bien été enregistré.' : 'Votre intérêt était déjà enregistré.');
} catch (Throwable $e) {
    flash('Votre intérêt n’a pas pu être enregistré. Merci de réessayer.', 'error');
}

redirect('demandes.php');
