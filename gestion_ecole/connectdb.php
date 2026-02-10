<?php
// Fichier : db_connect_ecole.php
// Configuration de la connexion à la base de données 'ecole'

function db_connect_ecole() {
    $servername = "localhost";
    $username = "root"; // Utilisez l'utilisateur MariaDB/MySQL approprié
    $password = "123"; // **!!! ATTENTION: Mettez votre vrai mot de passe root !!!**
    $dbname = "ecole";

    // Créer la connexion
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        // En cas d'échec, arrête l'exécution et affiche l'erreur
        die("Échec de la connexion à la base de données : " . $conn->connect_error);
    }
    
    // Assurer l'encodage UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Pour des raisons de sécurité, le mot de passe devrait être chargé depuis un fichier de configuration externe.
?>
