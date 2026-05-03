<?php
require_once 'includes/db.php';

$pdo = getPDO();

echo "<h2>🔐 Test d'authentification OMEGA Hôtel</h2>";
echo "<hr>";

// Tester la connexion avec admin123
$username = 'admin';
$password = 'admin123';

$stmt = $pdo->prepare("SELECT id, username, password, role FROM utilisateurs_hotel WHERE username = ? AND actif = 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

echo "<h3>Informations utilisateur:</h3>";
echo "<pre>";
print_r($user);
echo "</pre>";

if ($user) {
    echo "<p>✅ Utilisateur trouvé: <strong>{$user['username']}</strong> (role: {$user['role']})</p>";
    
    if (password_verify($password, $user['password'])) {
        echo "<p style='color:green; font-size:1.2rem; font-weight:bold'>✅ SUCCÈS ! Le mot de passe 'admin123' est CORRECT !</p>";
        echo "<p>Vous pouvez maintenant vous connecter avec :<br><strong>admin / admin123</strong></p>";
        
        // Simuler la session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        echo "<p>🔑 Session créée ! <a href='index.php'>Accéder au dashboard →</a></p>";
        
    } else {
        echo "<p style='color:red; font-weight:bold'>❌ ÉCHEC ! Le mot de passe 'admin123' ne correspond pas.</p>";
        echo "<p>Hash stocké: <code>{$user['password']}</code></p>";
        
        // Générer un nouveau hash
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "<p>Pour corriger, exécutez cette commande SQL :</p>";
        echo "<pre>UPDATE utilisateurs_hotel SET password = '$new_hash' WHERE username = 'admin';</pre>";
    }
} else {
    echo "<p style='color:red'>❌ Utilisateur 'admin' non trouvé !</p>";
}
?>
