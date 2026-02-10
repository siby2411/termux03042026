<?php
// /var/www/piece_auto/includes/auth_check.php

// Démarrer la session si elle ne l'est pas déjà
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté (existence de l'ID utilisateur)
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, le rediriger vers la page de connexion
    header("Location: /piece_auto/index.php"); // Adaptez ce chemin à votre page de login
    exit;
}

// Optionnel: vérifier le rôle si c'est nécessaire pour une page spécifique
// Ce reporting_strategique.php fait déjà la vérification de rôle plus loin.

?>
