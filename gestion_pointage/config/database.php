<?php
$host = '127.0.0.1'; // Utiliser l'IP locale est souvent plus stable sur proot
$dbname = 'gestion_pointages';
$username = 'root';
$password = '';
$socket = '/run/mysqld/mysqld.sock'; 

try {
    // Tentative via Socket (le plus rapide sur Linux)
    $pdo = new PDO("mysql:unix_socket=$socket;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    try {
        // Repli sur l'IP locale si le socket est inaccessible
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e2) {
        die("Erreur OMEGA Pointage : " . $e2->getMessage());
    }
}
?>
