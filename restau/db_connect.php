<?php
// Configuration OMEGA Universelle
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'restau';
$socket = '/var/run/mysqld/mysqld.sock';

// On force l'affichage des erreurs pour le debug
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($host, $user, $pass, $db, null, $socket);

if ($conn->connect_error) {
    // Tentative de secours sans le socket (TCP)
    $conn = @new mysqli('127.0.0.1', $user, $pass, $db);
}

if ($conn->connect_error) {
    die("❌ Erreur de Connexion : " . $conn->connect_error);
}

// Globalisation de la variable pour les scripts qui en dépendent
$GLOBALS['conn'] = $conn;
?>
