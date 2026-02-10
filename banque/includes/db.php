<?php
/**
 * Fichier de Configuration et Connexion à la Base de Données
 */

// --- Configuration ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '123');
define('DB_NAME', 'banque');

// --- Tentative de connexion à MariaDB/MySQL ---
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// --- 1. Vérification de la connexion ---
if ($conn->connect_error) {
    // En cas d'échec de connexion, arrêter l'application
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// --- 2. Configuration post-connexion ---

// A. Définir le jeu de caractères (Crucial pour les accents et les standards)
if (!$conn->set_charset("utf8mb4")) {
    error_log("Erreur lors du chargement du jeu de caractères utf8mb4: " . $conn->error);
}

// B. Définir le mode SQL de la session (Correction de l'erreur fatale)
// Utiliser TRADITIONAL garantit une gestion plus stricte des données (ex: pas d'insertion de NULL dans un NOT NULL).
$sql_mode_query = "SET SESSION sql_mode = 'TRADITIONAL'";
if (!$conn->query($sql_mode_query)) {
    // Utiliser error_log pour enregistrer l'erreur sans arrêter l'application si non fatal
    error_log("Erreur lors de la définition du mode SQL: " . $conn->error);
}

// Note : L'objet $conn est maintenant prêt à être utilisé dans toute l'application.

// Pour des raisons de performance et de clarté, nous pouvons inclure ici la fonction de gestion des clients
// si nous n'avons pas un fichier includes/fonctions.php séparé.
// Mais pour garder db.php propre, nous partons du principe que nous avons un fichier de fonctions séparé.
?>
