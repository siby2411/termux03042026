<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'librairie');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration de l'application
define('APP_NAME', 'OMEGA JUMTOU SAKOU KHAM KHAM TECH');
define('APP_VERSION', '2.0.0');

// Définition de BASE_URL (chemin absolu depuis la racine du site)
define('BASE_URL', '/');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('LOG_DIR', __DIR__ . '/../logs/');

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonctions d'authentification
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isCaissier() {
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'caissier' || $_SESSION['user_role'] === 'admin');
}

// Fonction de logging
function logActivity($action, $details = '') {
    global $pdo;
    $user_id = $_SESSION['user_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $stmt = $pdo->prepare("INSERT INTO logs (utilisateur_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $details, $ip]);
}

// Configuration du fuseau horaire
date_default_timezone_set('Africa/Dakar');

// Initialisation du système de notifications (uniquement après connexion)
require_once __DIR__ . '/notifications/notifications.php';
$notificationSystem = new NotificationSystem($pdo);

// Vérifier les alertes de stock pour l'admin
if (isset($_SESSION['user_id']) && isAdmin()) {
    $notificationSystem->checkStockAlert();
}
?>
