<?php
require_once __DIR__ . '/includes/layout.php';

if (is_admin()) {
    redirect('admin_formations.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Le formulaire a expiré. Merci de réessayer.';
    } elseif ($email === '' || $password === '') {
        $errors[] = 'L’adresse e-mail et le mot de passe sont obligatoires.';
    } else {
        $stmt = db()->prepare("SELECT id, password_hash FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            $errors[] = 'Identifiants administrateur incorrects.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $admin['id'];
            flash('Connexion administrateur réussie.');
            redirect('admin_formations.php');
        }
    }
}

render_header('Connexion administrateur');
?>
<section class="card auth-card">
    <h1>Connexion administrateur</h1>
    <p>La connexion est réservée aux administrateurs. Les visiteurs peuvent s’inscrire aux formations avec leur seule adresse e-mail.</p>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label for="email">Adresse e-mail</label>
        <input id="email" name="email" type="email" maxlength="190" autocomplete="username" required value="<?= e($email) ?>">

        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>

        <button class="btn" type="submit">Se connecter</button>
    </form>
</section>
<?php render_footer(); ?>
