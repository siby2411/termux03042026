<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    
    // Afficher la structure de la table users
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Structure de la table 'users' :\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} : {$col['Type']}\n";
    }
    
    // Afficher les utilisateurs existants
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nUtilisateurs existants :\n";
    foreach ($users as $user) {
        echo "  ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}\n";
    }
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
