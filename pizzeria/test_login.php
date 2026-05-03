<?php
require_once 'config/config.php';
$db = getDB();

echo "<h2>Test de connexion</h2>";

// Vérifier les caissiers
$cashiers = $db->query("SELECT id, username, full_name, password FROM cashiers")->fetchAll();

echo "<h3>Caissiers dans la base:</h3>";
foreach($cashiers as $c) {
    echo "ID: {$c['id']}, Username: {$c['username']}, Nom: {$c['full_name']}<br>";
    echo "Password hash: " . substr($c['password'], 0, 30) . "...<br>";
    $verify = password_verify('password123', $c['password']);
    echo "Vérification password123: " . ($verify ? "✅ OK" : "❌ KO") . "<br><br>";
}

// Tester la connexion
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $db->prepare("SELECT * FROM cashiers WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $cashier = $stmt->fetch();
    
    if ($cashier && password_verify($password, $cashier['password'])) {
        echo "<div style='color:green'>✅ Connexion réussie pour: " . $cashier['full_name'] . "</div>";
    } else {
        echo "<div style='color:red'>❌ Échec de connexion pour: $username</div>";
    }
}
?>

<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Tester</button>
</form>
