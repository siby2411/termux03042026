<?php
/**
 * CONFIGURATION GLOBALE DU PROJET REPORT
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Informations de connexion
$db_host = "localhost";
$db_name = "synthesepro_db";
$db_user = "root";
$db_pass = "123"; // à ajuster selon ton serveur

// Connexion PDO sécurisée
try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("❌ ERREUR CONNEXION BDD : " . $e->getMessage());
}

// Fonction pour vérifier rôle utilisateur
function require_role($roles = []) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        header('Location: login.php?error=unauthorized');
        exit();
    }
}

// Pas besoin du tag de fermeture PHP à la fin

