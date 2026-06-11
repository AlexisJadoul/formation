<?php
require_once __DIR__ . '/includes/layout.php';
$user = require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $description === '') {
        $errors[] = 'Le titre et la description sont obligatoires.';
    } else {
        $stmt = db()->prepare('INSERT INTO training_requests (user_id, title, description) VALUES (?, ?, ?)');
        $stmt->execute([$user['id'], $title, $description]);
        flash('Demande envoyée. Elle sera visible après validation par un administrateur.');
        redirect('demandes.php');
    }
}

render_header('Créer une demande');
?>
<div class="card form-card">
    <h1>Créer une demande de formation</h1>
    <p>
        Décris clairement le sujet, le besoin, le public concerné et ce que la formation pourrait apporter.
        La demande sera d'abord relue et validée par un administrateur.
    </p>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <label>Titre de la demande</label>
        <input type="text" name="title" required>

        <label>Description</label>
        <textarea name="description" rows="8" required></textarea>

        <button class="btn" type="submit">Envoyer la demande</button>
    </form>
</div>
<?php render_footer(); ?>
