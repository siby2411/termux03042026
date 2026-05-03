<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    
    // Vérifier si la colonne nom_complet existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'nom_complet'");
    if ($stmt->rowCount() == 0) {
        // Ajouter la colonne nom_complet
        $pdo->exec("ALTER TABLE users ADD COLUMN nom_complet VARCHAR(100) AFTER role");
        echo "✅ Colonne 'nom_complet' ajoutée\n";
    } else {
        echo "ℹ️ La colonne 'nom_complet' existe déjà\n";
    }
    
    // Vérifier si la colonne email existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($stmt->rowCount() == 0) {
        // Ajouter la colonne email
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER nom_complet");
        echo "✅ Colonne 'email' ajoutée\n";
    } else {
        echo "ℹ️ La colonne 'email' existe déjà\n";
    }
    
    // Mettre à jour les utilisateurs existants avec des valeurs par défaut si nécessaire
    $pdo->exec("UPDATE users SET nom_complet = CONCAT('User ', username) WHERE nom_complet IS NULL");
    $pdo->exec("UPDATE users SET email = CONCAT(username, '@centrediop.sn') WHERE email IS NULL");
    
    echo "✅ Structure de la table mise à jour avec succès\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
