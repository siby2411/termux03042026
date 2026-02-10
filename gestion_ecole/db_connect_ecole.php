<?php
function db_connect_ecole() {
    $host = "localhost";
    $user = "root";
    $pass = "123";
    $db   = "ecole";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Erreur connexion DB : " . $conn->connect_error);
    }

    return $conn;
}
?>

