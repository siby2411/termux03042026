<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>Test de connexion simple</h2>";

try {
    $pdo = getPDO();
    echo "<p style='color:green'>✅ Connexion DB OK</p>";
    
    // Récupérer l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['caissier1']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p style='color:green'>✅ Utilisateur trouvé</p>";
        echo "<ul>";
        echo "<li>ID: {$user['id']}</li>";
        echo "<li>Nom: {$user['prenom']} {$user['nom']}</li>";
        echo "<li>Rôle: {$user['role']}</li>";
        echo "<li>Hash: " . substr($user['password'], 0, 30) . "...</li>";
        echo "</ul>";
        
        // Formulaire de test
        if (isset($_POST['test_password'])) {
            $test_password = $_POST['test_password'];
            
            if (password_verify($test_password, $user['password'])) {
                echo "<p style='color:green'>✅ Mot de passe correct !</p>";
                
                // Démarrer une session de test
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_role'] = $user['role'];
                
                echo "<p><a href='modules/caisse/index.php'>Aller à la caisse</a></p>";
                
            } else {
                echo "<p style='color:red'>❌ Mot de passe incorrect</p>";
            }
        }
    } else {
        echo "<p style='color:red'>❌ Utilisateur non trouvé</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

?>

<form method="POST">
    <label>Tester le mot de passe:</label>
    <input type="password" name="test_password" value="caissier123">
    <button type="submit">Tester</button>
</form>

<p><a href="modules/auth/login.php">Aller à la page de login</a></p>
