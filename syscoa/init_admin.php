<?php
// Script d'initialisation admin
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la table utilisateurs existe
    $tableExists = $pdo->query("SHOW TABLES LIKE 'utilisateurs'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Créer la table utilisateurs
        $sql = "CREATE TABLE utilisateurs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            nom_complet VARCHAR(100) NOT NULL,
            role ENUM('admin', 'comptable', 'consultant', 'user') DEFAULT 'user',
            statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            UNIQUE KEY unique_username (username),
            UNIQUE KEY unique_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($sql);
        echo "✅ Table utilisateurs créée<br>";
    }
    
    // Vérifier si l'admin existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn() > 0;
    
    if (!$adminExists) {
        // Créer l'utilisateur admin
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO utilisateurs (username, email, password_hash, nom_complet, role) 
                VALUES ('admin', 'admin@syscohada.local', ?, 'Administrateur Système', 'admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$password_hash]);
        
        echo "✅ Utilisateur admin créé<br>";
        echo "🔑 Identifiants : admin / admin123<br>";
    } else {
        echo "✅ Utilisateur admin existe déjà<br>";
    }
    
    // Créer d'autres utilisateurs de test
    $testUsers = [
        ['comptable1', 'comptable@syscohada.local', 'Comptable Principal', 'comptable'],
        ['consultant1', 'consultant@syscohada.local', 'Consultant Financier', 'consultant'],
        ['user1', 'user@syscohada.local', 'Utilisateur Standard', 'user']
    ];
    
    foreach ($testUsers as $user) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE username = ?");
        $stmt->execute([$user[0]]);
        if ($stmt->fetchColumn() == 0) {
            $password_hash = password_hash('password123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO utilisateurs (username, email, password_hash, nom_complet, role) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute([$user[0], $user[1], $password_hash, $user[2], $user[3]]);
            echo "✅ Utilisateur {$user[0]} créé (mot de passe: password123)<br>";
        }
    }
    
    echo "<br>✅ Initialisation terminée<br>";
    echo "📋 <a href='login.php'>Accéder à la page de connexion</a><br>";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}
