<?php
/**
 * db_connect_ecole.php - Configuration OMEGA ÉCOLE 2026
 * Optimisé pour MariaDB via Socket (proot-distro)
 */

function db_connect_ecole() {
    $user = 'root';
    $pass = '123';
    $db   = 'ecole';
    // Chemin du socket MariaDB sur votre système
    $socket = '/var/run/mysqld/mysqld.sock';

    // On passe null pour l'hôte afin de forcer l'utilisation du socket
    $conn = new mysqli(null, $user, $pass, $db, null, $socket);

    if ($conn->connect_error) {
        // Secours si le socket est inaccessible
        $conn = new mysqli('127.0.0.1', $user, $pass, $db);
        if ($conn->connect_error) {
            die("Erreur de connexion OMEGA ÉCOLE : " . $conn->connect_error);
        }
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

// Initialisation de la timezone pour Dakar
date_default_timezone_set('Africa/Dakar');
?>
