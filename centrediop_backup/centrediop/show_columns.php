<?php
require_once 'config/database.php';

try {
    $pdo = getPDO();
    
    echo "📋 Structure de la table 'patients':\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("DESCRIBE patients");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        printf("  %-20s %-30s\n", $col['Field'], $col['Type']);
    }
    echo str_repeat("-", 50) . "\n";
    
    echo "\n📋 Structure de la table 'queue':\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("DESCRIBE queue");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        printf("  %-20s %-30s\n", $col['Field'], $col['Type']);
    }
    echo str_repeat("-", 50) . "\n";
    
    echo "\n📋 Structure de la table 'consultations':\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("DESCRIBE consultations");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        printf("  %-20s %-30s\n", $col['Field'], $col['Type']);
    }
    echo str_repeat("-", 50) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
