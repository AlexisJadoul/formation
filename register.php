<?php
require_once __DIR__ . '/includes/auth.php';

flash('La création de compte est désactivée : seuls les administrateurs disposent d’un compte.', 'info');
redirect('dashboard.php');
