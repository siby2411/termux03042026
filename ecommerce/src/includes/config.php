<?php

$host = "127.0.0.1";
$user = "root";
$pass = "123";   // ton mot de passe MariaDB !
$dbname = "ecommerce";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

