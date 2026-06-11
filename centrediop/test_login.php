<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

echo "=== TEST DE CONNEXION ===\n\n";

$username = 'dr.fall';
$password = 'pediatre123';

echo "Tentative de connexion avec:\n";
echo "Username: $username\n";
echo "Password: $password\n\n";

// Vérifier d'abord dans la base de données
$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ Utilisateur trouvé dans la BDD\n";
    echo "Nom: " . $user['prenom'] . " " . $user['nom'] . "\n";
    echo "Rôle: " . $user['role'] . "\n";
    
    // Vérifier le mot de passe
    if (password_verify($password, $user['password'])) {
        echo "✅ Mot de passe correct\n";
        
        // Simuler la connexion
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        
        echo "\n✅ Session créée avec succès\n";
        echo "Redirection vers: modules/medecin/dashboard.php\n";
    } else {
        echo "❌ Mot de passe incorrect\n";
        // Afficher le hash pour déboguer
        echo "Hash en BDD: " . $user['password'] . "\n";
    }
} else {
    echo "❌ Utilisateur non trouvé\n";
}

// Afficher le contenu de la session
echo "\n=== CONTENU DE LA SESSION ===\n";
print_r($_SESSION);
?>
