<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "=== TEST DES NOUVEAUX CAISSIERS ===\n\n";

$tests = [
    ['username' => 'caissier.diop', 'password' => 'caissier123', 'nom' => 'Oumar Diop'],
    ['username' => 'caissier.ndiaye', 'password' => 'caisse2026', 'nom' => 'Fatou Ndiaye'],
    ['username' => 'caissier.fall', 'password' => 'caisse123', 'nom' => 'Aminata Fall'],
];

foreach ($tests as $test) {
    echo "Test: {$test['username']} / {$test['password']}\n";
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$test['username']]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "  ✅ Utilisateur trouvé: {$user['prenom']} {$user['nom']} (ID: {$user['id']})\n";
        
        if (password_verify($test['password'], $user['password'])) {
            echo "  ✅ Mot de passe CORRECT - Connexion réussie!\n";
        } else {
            echo "  ❌ Mot de passe INCORRECT\n";
        }
    } else {
        echo "  ❌ Utilisateur non trouvé\n";
    }
    echo "\n";
}

echo "=== VOUS POUVEZ MAINTENANT VOUS CONNECTER AVEC CES IDENTIFIANTS ===\n";
?>
