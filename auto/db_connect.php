<?php
// Fichier: /var/www/auto/db_connect.php

$servername = "127.0.0.1"; // Ou l'adresse IP de votre serveur MySQL/MariaDB
$username = "root";       // Votre nom d'utilisateur MySQL/MariaDB
$password = ""; // Votre mot de passe MySQL/MariaDB (mettez le vrai)
$dbname = "auto"; // Le nom de votre base de données

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    // Utiliser un message d'erreur simple et en anglais ou avec des caractères ASCII de base
    // pour éviter les problèmes d'encodage au moment de l'affichage de l'erreur elle-même.
    // L'erreur originale venait du message PHP généré, pas de la connexion elle-même.
    die("Connection failed: " . $conn->connect_error);
    // Ou si vous voulez un message en français :
    // die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

// Optionnel: Définir le jeu de caractères de la connexion à utf8mb4 (recommandé)
// Ceci est très important pour la compatibilité avec les caractères spéciaux et emojis
if (!$conn->set_charset("utf8mb4")) {
    // En cas d'échec de la définition du charset, cela peut être un problème
    // mais la connexion elle-même ne devrait pas échouer à cause de ça initialement.
    // Pour cet exemple, on peut ignorer l'erreur ou la loguer.
    // error_log("Error loading character set utf8mb4: " . $conn->error);
}

// Note: Le message d'erreur d'origine est généré par PHP/MySQLi lui-même
// lorsqu'il essaie de formater le message d'erreur de la connexion
// avec un charset incompatible. En rendant votre message die() simple,
// on contourne le problème.
?>
