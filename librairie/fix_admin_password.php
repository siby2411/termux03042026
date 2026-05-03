<?php
require_once 'includes/config.php';

$username = 'admin';
$password = 'admin123';

echo "=== RÉINITIALISATION DU MOT DE PASSE ADMIN ===\n\n";

// Vérifier si l'utilisateur existe
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    echo "Utilisateur trouvé: " . $user['username'] . "\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Ancien hash: " . $user['password'] . "\n\n";
    
    // Tester l'ancien mot de passe
    if (password_verify($password, $user['password'])) {
        echo "✅ Le mot de passe actuel est déjà correct!\n";
    } else {
        echo "❌ Le mot de passe actuel est incorrect\n";
        echo "Génération d'un nouveau hash...\n";
        
        // Générer un nouveau hash
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "Nouveau hash: " . $new_hash . "\n\n";
        
        // Mettre à jour
        $update = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
        $update->execute([$new_hash, $user['id']]);
        
        echo "✅ Mot de passe mis à jour!\n";
    }
} else {
    echo "❌ Utilisateur admin non trouvé\n";
    echo "Création de l'utilisateur admin...\n";
    
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO utilisateurs (username, password, nom, prenom, role, statut) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->execute([$username, $new_hash, 'Administrateur', 'System', 'admin', 'actif']);
    
    echo "✅ Utilisateur admin créé!\n";
}

// Vérifier le résultat final
echo "\n=== VÉRIFICATION FINALE ===\n";
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    echo "✅ SUCCÈS! Vous pouvez maintenant vous connecter avec:\n";
    echo "   Utilisateur: admin\n";
    echo "   Mot de passe: admin123\n";
    echo "   URL: http://127.0.0.1:8000/login.php\n";
} else {
    echo "❌ ÉCHEC! Vérifions les détails:\n";
    echo "   Hash stocké: " . ($user['password'] ?? 'N/A') . "\n";
    echo "   Password test: " . ($user && password_verify($password, $user['password']) ? 'OK' : 'FAIL') . "\n";
}
?>
