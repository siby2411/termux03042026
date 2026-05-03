<?php
// Configuration OMEGA - Pièces Auto
$host = 'localhost';
$db   = 'piece_auto'; 
$user = 'root';
$pass = ''; 
$socket = '/var/run/mysqld/mysqld.sock';

try {
    $dsn = "mysql:host=$host;dbname=$db;unix_socket=$socket;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    // Correction du mode GROUP BY pour les inventaires
    $pdo->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
} catch (PDOException $e) {
    die("❌ Erreur Stock : " . $e->getMessage());
}
?>
