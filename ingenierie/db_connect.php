<?php
$host = 'localhost';
$db   = 'trading';
$user = 'root';
$pass = ''; // Votre mot de passe défini précédemment
$socket = '/run/mysqld/mysqld.sock'; // Chemin spécifique Termux Proot

try {
    $dsn = "mysql:unix_socket=$socket;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("❌ Erreur Trading DB : " . $e->getMessage());
}
?>
