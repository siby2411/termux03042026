<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Logique de bypass automatique pour la phase de test
$_SESSION['user_id'] = 1;
$_SESSION['email'] = 'admin@omega2026.com';
$_SESSION['role'] = 'admin';
$_SESSION['name'] = 'Expert Test';

// Redirection directe vers le dashboard principal
header("Location: admin_dashboard.php");
exit;
