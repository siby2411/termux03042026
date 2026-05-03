<?php
$host = '127.0.0.1';
$dbname = 'synthesepro_db';
$user = 'root';
$pass = '123';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
