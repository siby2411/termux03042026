<?php
// Activer toutes les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Test d'erreurs PHP</h1>";

// Tester config.php
echo "<h2>Test config.php</h2>";
if (file_exists('config.php')) {
    require_once 'config.php';
    echo "config.php chargé<br>";
    
    // Tester les constantes
    echo "SITE_NAME: " . (defined('SITE_NAME') ? SITE_NAME : 'NON DÉFINI') . "<br>";
    echo "DEFAULT_MODULE: " . (defined('DEFAULT_MODULE') ? DEFAULT_MODULE : 'NON DÉFINI') . "<br>";
} else {
    echo "ERREUR: config.php introuvable<br>";
}

// Tester header.php
echo "<h2>Test header.php</h2>";
if (file_exists('includes/header.php')) {
    echo "header.php existe<br>";
    
    // Vérifier la syntaxe PHP
    $output = shell_exec('php -l includes/header.php 2>&1');
    echo "Syntaxe header.php: " . nl2br(htmlspecialchars($output)) . "<br>";
} else {
    echo "ERREUR: header.php introuvable<br>";
}

// Tester index.php
echo "<h2>Test index.php</h2>";
$output = shell_exec('php -l index.php 2>&1');
echo "Syntaxe index.php: " . nl2br(htmlspecialchars($output));
