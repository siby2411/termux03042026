<?php
// Démarrer la session pour pouvoir la manipuler
session_start();

// Vider toutes les variables de session
$_SESSION = array();

// Détruire le cookie de session si il existe (optionnel mais recommandé)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Détruire la session côté serveur
session_destroy();

// Rediriger vers la page d'accueil ou de login
header("Location: index.php");
exit();
?>
