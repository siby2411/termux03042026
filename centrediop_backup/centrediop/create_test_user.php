<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    
    // Afficher les tables existantes pour vérifier
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables dans la base de données :\n";
    print_r($tables);
    
    // Vérifier si la table users existe
    if (!in_array('users', $tables)) {
        echo "❌ La table 'users' n'existe pas. Veuillez d'abord créer les tables.\n";
        exit;
    }
    
    // Vérifier si admin existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    
    if ($stmt->rowCount() == 0) {
        // Créer admin seulement s'il n'existe pas
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nom_complet, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $hashed_password, 'admin', 'Administrateur', 'admin@centrediop.sn']);
        echo "✅ Utilisateur admin créé avec succès\n";
    } else {
        echo "ℹ️ L'utilisateur admin existe déjà\n";
    }
    
    // Créer d'autres utilisateurs de test si nécessaire
    $test_users = [
        ['dr.fall', 'pediatre123', 'medecin', 'Dr. Fall', 'dr.fall@centrediop.sn'],
        ['sagefemme1', 'sagefemme123', 'sagefemme', 'Mme Diop', 'sagefemme@centrediop.sn'],
        ['caissier1', 'caissier123', 'caissier', 'M. Ndiaye', 'caissier@centrediop.sn']
    ];
    
    foreach ($test_users as $user) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user[0]]);
        
        if ($stmt->rowCount() == 0) {
            $hashed = password_hash($user[1], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nom_complet, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user[0], $hashed, $user[2], $user[3], $user[4]]);
            echo "✅ Utilisateur {$user[0]} créé\n";
        }
    }
    
    // Afficher les utilisateurs existants
    $stmt = $pdo->query("SELECT id, username, role, nom_complet FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 Utilisateurs disponibles :\n";
    foreach ($users as $user) {
        echo "  - {$user['username']} ({$user['role']}) : {$user['nom_complet']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
