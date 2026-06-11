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
    <script>
        (function () {
            var activeDialog = null;
            var dialogOpener = null;

            function supportsNativeDialog(dialog) {
                return typeof dialog.showModal === 'function' && typeof dialog.close === 'function';
            }

            function openDialog(dialog, opener) {
                if (!dialog || dialog.hasAttribute('open')) {
                    return;
                }

                activeDialog = dialog;
                dialogOpener = opener || document.activeElement;

                if (supportsNativeDialog(dialog)) {
                    dialog.showModal();
                    return;
                }

                var backdrop = document.createElement('div');
                backdrop.className = 'dialog-fallback-backdrop';
                backdrop.setAttribute('data-dialog-backdrop', '');
                dialog.parentNode.insertBefore(backdrop, dialog);
                dialog.setAttribute('open', '');
                dialog.setAttribute('aria-modal', 'true');
                dialog.classList.add('dialog-fallback-open');
                document.body.classList.add('dialog-open');

                var focusTarget = dialog.querySelector('input, textarea, select, button, [tabindex]:not([tabindex="-1"])');
                if (focusTarget) {
                    focusTarget.focus();
                }
            }

            function closeDialog(dialog) {
                if (!dialog) {
                    return;
                }

                if (supportsNativeDialog(dialog) && dialog.hasAttribute('open')) {
                    dialog.close();
                } else {
                    dialog.removeAttribute('open');
                }

                dialog.removeAttribute('aria-modal');
                dialog.classList.remove('dialog-fallback-open');

                var backdrop = dialog.previousElementSibling;
                if (backdrop && backdrop.hasAttribute('data-dialog-backdrop')) {
                    backdrop.remove();
                }

                document.body.classList.remove('dialog-open');
                activeDialog = null;

                if (dialogOpener && typeof dialogOpener.focus === 'function') {
                    dialogOpener.focus();
                }
                dialogOpener = null;
            }

            document.addEventListener('click', function (event) {
                var openButton = event.target.closest('[data-open-dialog]');
                if (openButton) {
                    openDialog(document.getElementById(openButton.getAttribute('data-open-dialog')), openButton);
                    return;
                }

                var closeButton = event.target.closest('[data-close-dialog]');
                if (closeButton) {
                    closeDialog(closeButton.closest('dialog'));
                    return;
                }

                if (event.target.hasAttribute('data-dialog-backdrop')) {
                    closeDialog(activeDialog);
                    return;
                }

                if (event.target.tagName === 'DIALOG') {
                    closeDialog(event.target);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && activeDialog && !supportsNativeDialog(activeDialog)) {
                    event.preventDefault();
                    closeDialog(activeDialog);
                }
            });

            document.querySelectorAll('dialog').forEach(function (dialog) {
                dialog.addEventListener('cancel', function (event) {
                    event.preventDefault();
                    closeDialog(dialog);
                });
            });

            document.querySelectorAll('dialog[data-open-on-load]').forEach(function (dialog) {
                openDialog(dialog, null);
            });
        }());
    </script>
    </body>
    </html>
    <?php
}
