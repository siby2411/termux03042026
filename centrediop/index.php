<?php
session_start();

// Rediriger vers la page de login si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: modules/auth/login.php');
    exit();
}

// Rediriger selon le rôle
require_once 'redirect.php';
?>
