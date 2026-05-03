<?php
session_start();

/**
 * Vérifie si l’utilisateur est connecté.
 * Si non, redirection vers login.php
 */

if (!isset($_SESSION['user_id'])) {
    header("Location: /public/login.php");
    exit;
}

