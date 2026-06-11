<?php
require_once __DIR__ . '/includes/auth.php';

flash('L’inscription aux créneaux est indisponible en mode consultation.', 'info');
redirect('formations.php');
