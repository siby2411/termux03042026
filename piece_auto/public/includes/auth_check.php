<?php
require_once __DIR__ . '/../includes/auth_check.php';
// includes/auth_check.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * SYSTÈME DE BYPASS "PIÈCE AUTO"
 * Force une session active pour éviter la redirection vers login.php
 */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Admin_Auto';
    $_SESSION['role'] = 'admin';
    $_SESSION['nom_complet'] = 'Administrateur Système';
}

// L'utilisateur est maintenant considéré comme authentifié par tous les scripts.
// Aucune redirection vers login.php n'est effectuée.
?>
