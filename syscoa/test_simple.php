<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Test PHP Simple</h1>";

// Test config.php
if (file_exists('config.php')) {
    require_once 'config.php';
    echo "config.php chargé<br>";
    
    // Test constantes
    echo "SITE_NAME: " . (defined('SITE_NAME') ? SITE_NAME : 'NON DÉFINI') . "<br>";
    echo "DEFAULT_MODULE: " . (defined('DEFAULT_MODULE') ? DEFAULT_MODULE : 'NON DÉFINI') . "<br>";
    echo "COMPANY_NAME: " . (defined('COMPANY_NAME') ? COMPANY_NAME : 'NON DÉFINI') . "<br>";
    
    // Test SYSCOHADA_MODULES
    if (defined('SYSCOHADA_MODULES')) {
        $modules = unserialize(SYSCOHADA_MODULES);
        echo "Modules définis: " . count($modules) . "<br>";
    }
} else {
    echo "ERREUR: config.php introuvable<br>";
}

// Test de base de données
echo "<h2>Test Base de données</h2>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sysco_ohada", "root", "123");
    echo "✅ Connexion MySQL réussie<br>";
    
    // Test tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . count($tables) . "<br>";
    
} catch (PDOException $e) {
    echo "❌ Erreur MySQL: " . $e->getMessage() . "<br>";
}
