<?php
require_once __DIR__ . '/includes/layout.php';

$errors = [];
$email = '';
$title = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mb_strtolower(trim($_POST['requester_email'] ?? ''));
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Le formulaire a expiré. Merci de réessayer.';
    }

    if ($email === '' || $title === '' || $description === '') {
        $errors[] = 'Tous les champs sont obligatoires.';
    }

    if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190)) {
        $errors[] = 'Adresse e-mail invalide.';
    }

    if (mb_strlen($title) > 190) {
        $errors[] = 'Le titre ne peut pas dépasser 190 caractères.';
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare('
                INSERT INTO training_requests (user_id, requester_email, title, description)
                VALUES (NULL, ?, ?, ?)
            ');
            $stmt->execute([$email, $title, $description]);

            flash('Votre demande de formation a bien été envoyée. Elle sera visible après validation.');
            redirect('demandes.php');
        } catch (Throwable $e) {
            $errors[] = 'La demande n’a pas pu être enregistrée. Merci de réessayer.';
        }
    }
}

render_header('Créer une demande de formation');
?>
<div class="page-title">
    <div>
        <h1>Créer une demande de formation</h1>
        <p>Proposez un besoin de formation sans créer de compte ni vous connecter.</p>
    </div>
</div>

<section class="card form-card">
    <p>Votre adresse e-mail suffit pour vous identifier. Elle sera uniquement visible par les administrateurs.</p>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label for="requester_email">Votre adresse e-mail</label>
        <input id="requester_email" name="requester_email" type="email" maxlength="190" autocomplete="email" required value="<?= e($email) ?>">

        <label for="title">Formation souhaitée</label>
        <input id="title" name="title" type="text" maxlength="190" required value="<?= e($title) ?>">

        <label for="description">Décrivez votre besoin</label>
        <textarea id="description" name="description" rows="7" required><?= e($description) ?></textarea>

        <button class="btn" type="submit">Envoyer ma demande</button>
    </form>
</section>
<?php render_footer(); ?>
