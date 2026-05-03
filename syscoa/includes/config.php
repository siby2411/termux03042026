<?php
// Configuration de la base de données
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sysco_ohada');
define('DB_USER', 'root');
define('DB_PASS', '123');

// Configuration des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction de connexion PDO
function getPDOConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    return $pdo;
}
?>
