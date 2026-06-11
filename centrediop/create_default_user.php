<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Vérifier si la table users existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE,
            password VARCHAR(255),
            nom VARCHAR(100),
            prenom VARCHAR(100),
            email VARCHAR(100),
            role VARCHAR(50) DEFAULT 'caissier',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Vérifier si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['caissier']);
    
    if (!$stmt->fetch()) {
        // Créer l'utilisateur par défaut
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, nom, prenom, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'caissier',
            md5('123456'), // Mot de passe hashé
            'Caisse',
            'Agent',
            'caissier'
        ]);
        
        echo "✅ Utilisateur caissier créé avec succès\n";
    } else {
        echo "ℹ️ L'utilisateur caissier existe déjà\n";
    }
    
    // Lister tous les utilisateurs
    echo "\nListe des utilisateurs:\n";
    $users = $pdo->query("SELECT id, username, nom, prenom, role FROM users")->fetchAll();
    foreach ($users as $user) {
        echo "- {$user['username']} ({$user['nom']} {$user['prenom']}) - {$user['role']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
