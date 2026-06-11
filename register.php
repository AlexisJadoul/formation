<?php
require_once __DIR__ . '/includes/layout.php';

if (current_user()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresse email invalide.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "agent")');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            flash('Compte créé. Tu peux maintenant te connecter.');
            redirect('login.php');
        } catch (PDOException $e) {
            $errors[] = 'Cette adresse email est déjà utilisée.';
        }
    }
}

render_header('Créer un compte');
?>
<section class="auth-page">
    <div class="card auth-card">
        <h1>Créer un compte agent</h1>

        <?php foreach ($errors as $error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post">
            <label>Nom complet</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required minlength="8">

            <button class="btn" type="submit">Créer mon compte</button>
        </form>
    </div>
</section>
<?php render_footer(); ?>
