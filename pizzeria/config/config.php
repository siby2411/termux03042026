<?php
session_start();
date_default_timezone_set('Africa/Dakar');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost:8080');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_PATH', BASE_PATH . '/uploads');

if (!defined('CURRENCY')) define('CURRENCY', 'XOF');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', 'CFA');

define('DB_HOST', 'localhost');
define('DB_NAME', 'pizzeria_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('COMPANY_NAME', 'Omega Pizzeria');
define('COMPANY_PHONE', '77 654 28 03');
define('COMPANY_EMAIL', 'contact@omegapizzeria.sn');
define('DELIVERY_FEE', 1000);

function getDB() {
    static $pdo = null;
    if (!$pdo) {
        try {
            $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Erreur DB: " . $e->getMessage());
        }
    }
    return $pdo;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' ' . CURRENCY_SYMBOL;
}

function generateCode($prefix) {
    return $prefix . date('Ymd') . rand(1000, 9999);
}

function getCartCount() {
    $count = 0;
    if(isset($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}
?>
