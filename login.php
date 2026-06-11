<?php
require_once __DIR__ . '/includes/layout.php';

if (current_user()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        redirect('dashboard.php');
    }

    $error = 'Identifiants incorrects.';
}

render_header('Connexion');
?>
<section class="auth-page">
    <div class="card auth-card">
        <h1>Connexion</h1>
        <p>Connecte-toi pour consulter les créneaux, créer une demande ou t'inscrire à une formation.</p>

        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required>

            <button class="btn" type="submit">Se connecter</button>
        </form>

        <p class="small">Pas encore de compte ? <a href="register.php">Créer un compte agent</a></p>
    </div>
</section>
<?php render_footer(); ?>
