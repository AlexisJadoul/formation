<?php
require_once __DIR__ . '/auth.php';

function render_header(string $title): void
{
    $flash = flash();
    $user = current_user();
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> - <?= e(APP_NAME) ?></title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
    <header class="topbar">
        <div class="brand">
            <a href="dashboard.php"><?= e(APP_NAME) ?></a>
        </div>
        <nav>
            <a href="dashboard.php">Accueil</a>
            <a href="demandes.php">Demandes</a>
            <a href="demande_create.php">Créer une demande</a>
            <a href="formations.php">Créneaux</a>
            <?php if (is_admin($user)): ?>
                <a href="admin_demandes.php">Gérer les demandes</a>
                <a href="admin_formations.php">Gérer les créneaux</a>
                <a href="logout.php">Déconnexion</a>
                <span class="visitor-badge">Administrateur</span>
            <?php else: ?>
                <span class="visitor-badge">Accès public</span>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">
        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <footer class="footer">
        
    </footer>
    </body>
    </html>
    <?php
}
