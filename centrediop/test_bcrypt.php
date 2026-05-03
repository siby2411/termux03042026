<?php
require_once 'config/database.php';

echo "=== TEST DE CONNEXION AVEC BCRYPT ===\n\n";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Récupérer l'utilisateur caissier1
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['caissier1']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Utilisateur trouvé:\n";
        echo "  ID: {$user['id']}\n";
        echo "  Username: {$user['username']}\n";
        echo "  Nom: {$user['prenom']} {$user['nom']}\n";
        echo "  Role: {$user['role']}\n";
        echo "  Password hash: {$user['password']}\n\n";
        
        // Tester le mot de passe
        $test_password = 'caissier123';
        echo "Test du mot de passe 'caissier123':\n";
        
        if (password_verify($test_password, $user['password'])) {
            echo "✅ SUCCÈS: Le mot de passe est valide !\n";
            
            // Simuler une connexion
            echo "\nSession simulée:\n";
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_role'] = $user['role'];
            echo "  User ID: {$_SESSION['user_id']}\n";
            echo "  User Name: {$_SESSION['user_name']}\n";
            echo "  User Role: {$_SESSION['user_role']}\n";
            
        } else {
            echo "❌ ÉCHEC: Mot de passe incorrect\n";
            
            // Afficher les infos de débogage
            echo "\nInformations de débogage:\n";
            echo "  Hash stocké: {$user['password']}\n";
            echo "  Longueur hash: " . strlen($user['password']) . "\n";
            echo "  Type de hash: " . (strpos($user['password'], '$2y$') === 0 ? 'bcrypt' : 'inconnu') . "\n";
        }
    } else {
        echo "❌ Utilisateur 'caissier1' non trouvé\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
