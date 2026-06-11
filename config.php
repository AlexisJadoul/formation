<?php
// Configuration MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'plateforme_formations');
define('DB_USER', 'root');
define('DB_PASS', '');

// Nom de l'application
define('APP_NAME', 'Plateforme de formations');

// Mettre à true en développement, false en production
define('APP_DEBUG', true);

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
}
