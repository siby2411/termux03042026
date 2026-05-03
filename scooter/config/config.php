<?php
session_start();
date_default_timezone_set('Africa/Dakar');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost:8080');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// Éviter les redéfinitions de constantes
if (!defined('CURRENCY')) define('CURRENCY', 'XOF');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', 'CFA');

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'scooter_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB() {
    static $pdo = null;
    if (!$pdo) {
        try {
            $dsn = "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Erreur DB: " . $e->getMessage());
        }
    }
    return $pdo;
}

function isLoggedIn() { return isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true; }
function redirect($url) { header("Location: " . BASE_URL . $url); exit(); }
function formatPrice($price) { return number_format($price, 0, ',', ' ') . ' ' . CURRENCY_SYMBOL; }
function generateCode($prefix) { return $prefix . date('Ymd') . rand(1000, 9999); }
?>
