<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect_ecole.php';

echo "Test function: ";
var_dump(function_exists("db_connect_ecole"));

echo "<br>Connexion: ";
$conn = db_connect_ecole();
echo "OK";

