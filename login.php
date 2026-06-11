<?php
require_once __DIR__ . '/includes/auth.php';

flash('La connexion est désactivée : la plateforme est accessible librement en mode consultation.', 'info');
redirect('dashboard.php');
