<?php
require_once 'includes/config.php';

echo "=== VÉRIFICATION DES UTILISATEURS ===\n\n";

$users = $pdo->query("SELECT id, username, nom, prenom, role, statut FROM utilisateurs")->fetchAll();

foreach($users as $user) {
    echo "ID: {$user['id']}\n";
    echo "Username: {$user['username']}\n";
    echo "Nom: {$user['prenom']} {$user['nom']}\n";
    echo "Rôle: {$user['role']}\n";
    echo "Statut: {$user['statut']}\n";
    
    // Tester le mot de passe admin123
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user['id']]);
    $user_data = $stmt->fetch();
    
    $test = password_verify('admin123', $user_data['password']);
    echo "Mot de passe 'admin123': " . ($test ? "✅ CORRECT" : "❌ INCORRECT") . "\n";
    echo str_repeat("-", 50) . "\n\n";
}
?>
