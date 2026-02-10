

<?php
// Fichier : db_connect.php
// Définit et retourne l'objet de connexion MariaDB/MySQL

// Paramètres de connexion
$servername = "localhost"; // MariaDB/MySQL sur WSL
$username = "momo";        // Utilisateur que vous avez configuré
$password = "siby";        // Mot de passe de l'utilisateur momo
$dbname = "gestion";       // Votre base de données

// Fonction pour établir et retourner la connexion
function db_connect() {
    global $servername, $username, $password, $dbname;
    
    // Utilisation de mysqli pour la connexion orientée objet
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        // En cas d'échec, arreter le script et afficher l'erreur
        die("❌ Échec de la connexion à la base de données : " . $conn->connect_error);
    }
    
    // Si la connexion réussit, la retourner
    return $conn;
}

// Optionnel: Fonction pour hacher les mots de passe avant l'insertion/mise à jour
function hash_password($password) {
    // Utilise l'algorithme par défaut (recommandé : bcrypt)
    return password_hash($password, PASSWORD_DEFAULT);
}
?>



