<?php
require_once 'includes/config.php';

$username = 'admin';
$password = 'admin123';

// Vérifier l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    echo "Utilisateur trouvé: " . $user['username'] . "<br>";
    echo "Rôle: " . $user['role'] . "<br>";
    echo "Hash stocké: " . $user['password'] . "<br>";
    
    if (password_verify($password, $user['password'])) {
        echo "✅ Mot de passe correct !<br>";
        echo "Vous pouvez vous connecter avec:<br>";
        echo "URL: http://127.0.0.1:8000/login.php<br>";
        echo "Login: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "❌ Mot de passe incorrect<br>";
        echo "Génération d'un nouveau hash...<br>";
        
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "Nouveau hash: " . $new_hash . "<br>";
        
        $update = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE username = ?");
        $update->execute([$new_hash, $username]);
        
        echo "✅ Mot de passe réinitialisé !<br>";
        echo "Essayez maintenant de vous connecter.<br>";
    }
} else {
    echo "❌ Utilisateur admin non trouvé<br>";
    echo "Création de l'utilisateur admin...<br>";
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO utilisateurs (username, password, nom, prenom, role, statut) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->execute([$username, $hash, 'Administrateur', 'System', 'admin', 'actif']);
    
    echo "✅ Utilisateur admin créé !<br>";
    echo "Login: admin<br>";
    echo "Password: admin123<br>";
}
?>
