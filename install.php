<?php
require_once __DIR__ . '/config.php';

$errors = [];
$success = false;

try {
    $pdoServer = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdoServer->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
} catch (Throwable $e) {
    $errors[] = 'Connexion MySQL impossible : ' . $e->getMessage();
}

require_once __DIR__ . '/includes/db.php';

if (!$errors) {
    $sql = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin') NOT NULL DEFAULT 'admin',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS training_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            requester_email VARCHAR(190) NOT NULL,
            title VARCHAR(190) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            admin_comment TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS request_votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_vote (request_id, user_id),
            FOREIGN KEY (request_id) REFERENCES training_requests(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS training_slots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(190) NOT NULL,
            description TEXT NOT NULL,
            trainer VARCHAR(190) NULL,
            location VARCHAR(190) NULL,
            start_at DATETIME NOT NULL,
            end_at DATETIME NOT NULL,
            capacity INT NOT NULL DEFAULT 10,
            created_by INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS slot_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slot_id INT NOT NULL,
            participant_email VARCHAR(190) NOT NULL,
            status ENUM('registered', 'cancelled') NOT NULL DEFAULT 'registered',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_registration_email (slot_id, participant_email),
            FOREIGN KEY (slot_id) REFERENCES training_slots(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS slot_interests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slot_id INT NOT NULL,
            participant_email VARCHAR(190) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_interest_email (slot_id, participant_email),
            FOREIGN KEY (slot_id) REFERENCES training_slots(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    foreach ($sql as $query) {
        db()->exec($query);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors) {
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
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE role = "admin"');
        $stmt->execute();
        $adminExists = (int) $stmt->fetchColumn() > 0;

        if ($adminExists) {
            $errors[] = 'Un compte administrateur existe déjà.';
        } else {
            $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "admin")');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $success = true;
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Installation</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="auth-page">
    <section class="card auth-card">
        <h1>Installation</h1>
        <p>Les tables sont créées automatiquement. Crée maintenant le premier compte administrateur.</p>

        <?php foreach ($errors as $error): ?>
            <div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>

        <?php if ($success): ?>
            <div class="alert success">Installation terminée. Tu peux maintenant supprimer install.php et consulter la plateforme.</div>
            <a class="btn" href="dashboard.php">Voir la plateforme</a>
        <?php else: ?>
            <form method="post">
                <label>Nom</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Mot de passe</label>
                <input type="password" name="password" required minlength="8">

                <button class="btn" type="submit">Créer le compte administrateur</button>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
