<?php
$host = 'localhost';
$dbname = 'synthesepro_db';  // on utilise la même BDD existante
$user = 'root';
$pass = '123';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
?>
