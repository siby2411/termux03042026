<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Vérification des mots de passe</h2>";

$users = $conn->query("SELECT id, username, role, password FROM users")->fetchAll();

foreach ($users as $user) {
    echo "<h3>Utilisateur: {$user['username']} ({$user['role']})</h3>";
    echo "Hash stocké: {$user['password']}<br>";
    
    // Tester avec différents mots de passe
    $passwords_to_test = ['123456', 'admin123', 'caissier123', 'pediatre123'];
    
    foreach ($passwords_to_test as $test_password) {
        if (password_verify($test_password, $user['password'])) {
            echo "<span style='color:green'>✅ Mot de passe '{$test_password}' VALIDE</span><br>";
        }
    }
    
    // Si aucun mot de passe ne correspond, on peut en générer un nouveau
    if (!password_verify('123456', $user['password']) && 
        !password_verify('admin123', $user['password']) && 
        !password_verify('caissier123', $user['password'])) {
        echo "<span style='color:orange'>⚠️ Aucun mot de passe standard ne correspond</span><br>";
        
        // Générer un nouveau hash
        $new_password = '123456';
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        echo "Nouveau hash proposé: {$new_hash}<br>";
    }
    
    echo "<hr>";
}
?>
