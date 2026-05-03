<?php
$host = '127.0.0.1';
$dbname = 'reporting_db';
$user = 'root';
$pass = ''; 

try {
    // Connexion ultra-minimaliste (sans charset dans le DSN)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    
    // On définit l'encodage via une commande SQL après la connexion
    $pdo->exec("SET NAMES 'utf8'");
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn = $pdo; 
} catch(PDOException $e) {
    // Message d'erreur propre pour le diagnostic
    die("❌ ERREUR DE CONNEXION : " . $e->getMessage());
}
