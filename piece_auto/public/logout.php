<?php
// /var/www/piece_auto/public/logout.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// CORRECTION: Utiliser le chemin correct pour globals.php
include '../config/globals.php';

// Détruire toutes les variables de session
$_SESSION = array();

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Redirection vers la page de login
header('Location: ' . $GLOBALS['app_root'] . '/login.php');
exit;
?>
