<?php
/**
 * CONFIGURATION UNIFIÉE - SYNTHESEPRO OHADA
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Paramètres de connexion
$db_host = "127.0.0.1";
$db_port = "3306";
$db_name = "synthesepro_db"; // Assurez-vous que c'est le bon nom
$db_user = "root";
$db_pass = ""; // Laissez vide si vous n'avez pas mis "123" dans MariaDB

try {
    // Connexion via TCP/IP (plus stable sur proot-distro)
    $pdo = new PDO(
        "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Message d'erreur détaillé pour le debug sur Termux
    die("❌ ERREUR CONNEXION SYNTHESEPRO : " . $e->getMessage() . " (Vérifiez si service mariadb start est lancé)");
}

// Helper pour la sécurité des rôles SYSCOHADA
function require_role($roles = []) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        header('Location: login.php?error=unauthorized');
        exit();
    }
}
