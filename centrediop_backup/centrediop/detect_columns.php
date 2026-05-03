<?php
require_once 'config/database.php';

try {
    $pdo = getPDO();
    
    echo "📋 Structure de la table 'patients':\n";
    $stmt = $pdo->query("DESCRIBE patients");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        echo "  - {$col['Field']} : {$col['Type']}\n";
    }
    
    echo "\n📋 Structure de la table 'queue':\n";
    $stmt = $pdo->query("DESCRIBE queue");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        echo "  - {$col['Field']} : {$col['Type']}\n";
    }
    
    echo "\n📋 Structure de la table 'consultations':\n";
    $stmt = $pdo->query("DESCRIBE consultations");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        echo "  - {$col['Field']} : {$col['Type']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
