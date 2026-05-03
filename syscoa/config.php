<?php
// Configuration SYSCOHADA - Version corrigée

// === CONFIGURATION DE LA BASE DE DONNÉES ===
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sysco_ohada');
define('DB_USER', 'root');
define('DB_PASS', '123');

// === CONFIGURATION DE L'APPLICATION ===
define('SYSCOHADA_VERSION', '2.0');
define('SITE_NAME', 'SYSCOHADA v2.0');
define('SITE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1') . '/syscoa/');
define('COMPANY_NAME', 'SYSCOHADA Entreprise');
define('COMPANY_ADDRESS', '123 Rue des Comptables, Ville');
define('COMPANY_PHONE', '+225 01 23 45 67 89');
define('COMPANY_EMAIL', 'contact@syscohada.local');
define('COMPANY_SLOGAN', 'Votre solution comptable OHADA');

// === CONFIGURATION DES MODULES ===
define('DEFAULT_MODULE', 'dashboard');
define('DEFAULT_SUBMODULE', '');

// === DÉFINITION DES MODULES DU MENU ===
define('SYSCOHADA_MODULES', serialize([
    'dashboard' => ['Tableau de bord', 'fas fa-tachometer-alt'],
    'journaux' => ['Journaux', 'fas fa-book'],
    'journal' => ['Journal', 'fas fa-book'],
    'grand_livre' => ['Grand Livre', 'fas fa-file-invoice'],
    'balance' => ['Balance', 'fas fa-balance-scale'],
    'comptes' => ['Plan Comptable', 'fas fa-chart-line'],
    'sig' => ['SIG', 'fas fa-chart-bar'],
    'ratios' => ['Ratios', 'fas fa-percentage'],
    'budget' => ['Budget', 'fas fa-money-check-alt'],
    'tiers' => ['Tiers', 'fas fa-users'],
    'rapports' => ['Rapports', 'fas fa-file-contract'],
    'etats' => ['États Financiers', 'fas fa-chart-line'],
    'parametres' => ['Paramètres', 'fas fa-cogs']
]));

// === CONFIGURATION FINANCIÈRE ===
define('CURRENCY', 'FCFA');
define('DATE_FORMAT', 'd/m/Y');
define('DECIMAL_SEPARATOR', ',');
define('THOUSAND_SEPARATOR', ' ');
define('DECIMAL_PLACES', 2);

// === CONFIGURATION DU SYSTÈME ===
define('ITEMS_PER_PAGE', 20);
define('SESSION_TIMEOUT', 3600); // 1 heure
define('DEBUG_MODE', true);

// === FONCTIONS UTILITAIRES ===
function check_auth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function check_login() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function get_db_connection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}

// === DÉBUT DE SESSION ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion automatique à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");
} catch (PDOException $e) {
    // Ne pas afficher l'erreur en production
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}
