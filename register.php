<?php
require_once __DIR__ . '/includes/auth.php';

flash('La création de compte est désactivée : aucune inscription n’est nécessaire pour consulter la plateforme.', 'info');
redirect('dashboard.php');
