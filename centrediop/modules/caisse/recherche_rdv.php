<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'caissier') {
    header('Location: ../auth/login.php');
    exit();
}

// Redirection vers l'index pour l'instant
header('Location: index.php#rdvSection');
exit();
?>
