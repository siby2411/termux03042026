<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    
    // Vérifier les colonnes disponibles
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Colonnes disponibles : " . implode(', ', $columns) . "\n";
    
    // Vérifier si admin existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    
    if ($stmt->rowCount() == 0) {
        // Déterminer les colonnes disponibles pour l'insertion
        $insert_cols = ['username', 'password', 'role'];
        $insert_vals = ['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin'];
        
        // Ajouter les colonnes optionnelles si elles existent
        if (in_array('nom_complet', $columns)) {
            $insert_cols[] = 'nom_complet';
            $insert_vals[] = 'Administrateur';
        }
        if (in_array('email', $columns)) {
            $insert_cols[] = 'email';
            $insert_vals[] = 'admin@centrediop.sn';
        }
        if (in_array('name', $columns)) {
            $insert_cols[] = 'name';
            $insert_vals[] = 'Administrateur';
        }
        if (in_array('full_name', $columns)) {
            $insert_cols[] = 'full_name';
            $insert_vals[] = 'Administrateur';
        }
        
        // Construire la requête
        $placeholders = implode(',', array_fill(0, count($insert_cols), '?'));
        $sql = "INSERT INTO users (" . implode(',', $insert_cols) . ") VALUES (" . $placeholders . ")";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($insert_vals);
        echo "✅ Utilisateur admin créé avec succès\n";
    } else {
        echo "ℹ️ L'utilisateur admin existe déjà\n";
    }
    
    // Afficher les utilisateurs existants
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 Utilisateurs disponibles :\n";
    foreach ($users as $user) {
        $name = isset($user['nom_complet']) ? $user['nom_complet'] : 
                (isset($user['name']) ? $user['name'] : 
                (isset($user['full_name']) ? $user['full_name'] : 'Nom non disponible'));
        echo "  - {$user['username']} ({$user['role']}) : {$name}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
