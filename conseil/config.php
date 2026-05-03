<?php
session_start();
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'conseil_velingara');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur connexion BDD: " . $e->getMessage());
}
define('PRESIDENT', 'Ibrahima Barry');
define('DIRECTEUR', 'Mohamed Siby');
define('TEL_DIRECTEUR', '77 654 28 03');
?>
