<?php
require_once __DIR__ . '/includes/auth.php';

flash('La création de demandes est indisponible en mode consultation.', 'info');
redirect('demandes.php');
