<?php
function db_connect() {
    $host = "127.0.0.1";
    $user = "root";
    $pass = "";
    $dbname = "gestion_commerciale";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Erreur de connexion : " . $conn->connect_error);
    }
    return $conn;
}
?>
