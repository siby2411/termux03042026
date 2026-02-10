<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if ($conn) {
    echo "Connexion OK !";
}
?>

