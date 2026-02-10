<?php
session_start();

// 1. Si l'utilisateur est déjà connecté, on le renvoie au dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard_pilote.php');
    exit;
}

// 2. Si l'utilisateur n'est PAS connecté (cas par défaut) :
//    => On affiche le formulaire de connexion ci-dessous.
?>
