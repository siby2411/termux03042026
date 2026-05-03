<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Vérifier si admin existe
    $stmt = $pdo->query("SELECT id, username, password, role FROM users WHERE username = 'admin'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "Admin trouvé (ID: {$admin['id']})\n";
        
        // Tester le mot de passe actuel
        $test_password = 'admin123';
        if (password_verify($test_password, $admin['password'])) {
            echo "✅ Le mot de passe 'admin123' est correct\n";
        } else {
            echo "❌ Le mot de passe 'admin123' est incorrect\n";
            echo "Hash actuel : " . $admin['password'] . "\n";
            
            // Réinitialiser le mot de passe
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            $update->execute([$new_hash]);
            echo "✅ Mot de passe admin réinitialisé à 'admin123'\n";
        }
    } else {
        echo "❌ Admin non trouvé dans la base de données\n";
        
        // Créer admin s'il n'existe pas
        $service_id = 1; // ID par défaut
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (username, password, role, nom_complet, email, service_id) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute(['admin', $new_hash, 'admin', 'Administrateur', 'admin@centrediop.sn', $service_id]);
        echo "✅ Admin créé avec succès\n";
    }
    
    // Afficher tous les utilisateurs
    echo "\n📋 Liste des utilisateurs :\n";
    $stmt = $pdo->query("SELECT id, username, role, nom_complet FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $u) {
        echo "  - {$u['id']}: {$u['username']} ({$u['role']}) - {$u['nom_complet']}\n";
    }
    
    echo "\n🔑 Essayez de vous connecter avec :\n";
    echo "   admin / admin123\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
