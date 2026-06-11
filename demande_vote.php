<?php
require_once __DIR__ . '/includes/auth.php';

flash('Le vote est indisponible en mode consultation.', 'info');
redirect('demandes.php');
