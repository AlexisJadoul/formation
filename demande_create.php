<?php
require_once __DIR__ . '/includes/layout.php';

$errors = [];
$email = '';
$title = '';
$description = '';
$targetAudience = '';
$prerequisites = '';
$objectives = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mb_strtolower(trim($_POST['requester_email'] ?? ''));
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $targetAudience = trim($_POST['target_audience'] ?? '');
    $prerequisites = trim($_POST['prerequisites'] ?? '');
    $objectives = trim($_POST['objectives'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Le formulaire a expiré. Merci de réessayer.';
    }

    if ($email === '' || $title === '' || $description === '') {
        $errors[] = 'L’adresse e-mail, le titre et la description sont obligatoires.';
    }

    if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190)) {
        $errors[] = 'Adresse e-mail invalide.';
    }

    if (mb_strlen($title) > 190) {
        $errors[] = 'Le titre ne peut pas dépasser 190 caractères.';
    }

    if (!$errors) {
        try {
            $descriptionSections = [];
            foreach ([
                'Public visé' => $targetAudience,
                'Prérequis' => $prerequisites,
                'Objectif' => $objectives,
                'Contenu' => $content,
            ] as $heading => $value) {
                if ($value !== '') {
                    $descriptionSections[] = $heading . " :\n" . $value;
                }
            }

            $completeDescription = $description;
            if ($descriptionSections) {
                $completeDescription .= "\n\n" . implode("\n\n", $descriptionSections);
            }

            $stmt = db()->prepare('
                INSERT INTO training_requests (user_id, requester_email, title, description)
                VALUES (NULL, ?, ?, ?)
            ');
            $stmt->execute([$email, $title, $completeDescription]);

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
    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label for="title">Formation souhaitée</label>
        <input id="title" name="title" type="text" maxlength="190" required value="<?= e($title) ?>">

        <label for="description">Décrivez votre besoin</label>
        <textarea id="description" name="description" rows="7" required><?= e($description) ?></textarea>

        <h2>Informations complémentaires</h2>
        <p class="form-help">Ces champs sont facultatifs. Seuls les champs renseignés seront ajoutés à la description de la formation.</p>

        <label for="target_audience">Public visé</label>
        <textarea id="target_audience" name="target_audience" rows="3"><?= e($targetAudience) ?></textarea>

        <label for="prerequisites">Prérequis</label>
        <textarea id="prerequisites" name="prerequisites" rows="3"><?= e($prerequisites) ?></textarea>

        <label for="objectives">Objectif</label>
        <textarea id="objectives" name="objectives" rows="3"><?= e($objectives) ?></textarea>

        <label for="content">Contenu</label>
        <textarea id="content" name="content" rows="4"><?= e($content) ?></textarea>

        <button class="btn" type="button" data-open-dialog="request-email-dialog">Envoyer ma demande</button>

        <dialog class="email-dialog" id="request-email-dialog" aria-labelledby="request-email-title" <?= $errors ? 'data-open-on-load' : '' ?>>
            <div class="dialog-heading">
                <h2 id="request-email-title">Votre adresse e-mail</h2>
                <button class="dialog-close" type="button" data-close-dialog aria-label="Fermer">×</button>
            </div>
            <p>Votre adresse permet de vous identifier et sera uniquement visible par les administrateurs.</p>
            <label for="requester_email">Votre adresse e-mail</label>
            <input id="requester_email" name="requester_email" type="email" maxlength="190" autocomplete="email" inputmode="email" required value="<?= e($email) ?>">
            <button class="btn" type="submit">Confirmer et envoyer</button>
        </dialog>
    </form>
</section>
<?php render_footer(); ?>
