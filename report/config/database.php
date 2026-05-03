<?php
// OMEGA CONFIG - REPORTING OHADA
define('DB_HOST', 'localhost');
define('DB_NAME', 'reporting_db'); // Vérifiez le nom de votre base report
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_SOCKET', '/var/run/mysqld/mysqld.sock');

try {
    $dsn = "mysql:host=".DB_HOST.";unix_socket=".DB_SOCKET.";dbname=".DB_NAME.";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("❌ ERREUR CONNEXION BDD OMEGA : " . $e->getMessage());
}
?>
