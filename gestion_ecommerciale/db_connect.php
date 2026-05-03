<?php
/**
 * Fichier : db_connect.php
 * Module : Gestion E-Commerciale Omega
 * Description : Connexion centralisée à MariaDB via l'IP 127.0.0.1
 */

// Configuration des accès
$servername = "127.0.0.1"; // Utilisation de l'IP pour éviter 'localhost' (Socket Unix)
$username   = "momo";      // Ton utilisateur MariaDB
$password   = "siby";      // Ton mot de passe MariaDB
$dbname     = "gestion";   // Ta base de données
$port       = 3306;        // Port standard MariaDB

/**
 * Fonction universelle pour établir la connexion
 * @return mysqli l'objet de connexion
 */
function db_connect() {
    global $servername, $username, $password, $dbname, $port;
    
    // Désactiver le rapport d'erreur strict de mysqli pour gérer les erreurs nous-mêmes
    mysqli_report(MYSQLI_REPORT_OFF); 

    try {
        // Le @ masque les warnings système si la connexion échoue
        $conn = @new mysqli($servername, $username, $password, $dbname, $port);

        // Vérification d'une erreur de connexion
        if ($conn->connect_error) {
            die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red;'>
                    <h3>❌ Erreur de Connexion à la Base de Données</h3>
                    <p>Détails : " . $conn->connect_error . " (Code : " . $conn->connect_errno . ")</p>
                    <p>Vérifiez que MariaDB est bien lancé et que l'utilisateur 'momo' a les droits.</p>
                 </div>");
        }

        // Définir l'encodage en UTF-8 pour éviter les problèmes d'accents
        $conn->set_charset("utf8mb4");

        return $conn;
        
    } catch (Exception $e) {
        die("❌ Une erreur critique est survenue : " . $e->getMessage());
    }
}

/**
 * Fonction utilitaire pour hacher un mot de passe (si besoin)
 */
function hash_password($pass) {
    return password_hash($pass, PASSWORD_DEFAULT);
}
?>
