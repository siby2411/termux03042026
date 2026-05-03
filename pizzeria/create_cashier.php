<?php
require_once 'config/config.php';
$db = getDB();

echo "<h2>Création d'un nouveau caissier</h2>";

$username = 'test';
$password = 'password123';
$full_name = 'Test User';
$hashed = password_hash($password, PASSWORD_DEFAULT);

echo "Hash généré: <code>$hashed</code><br><br>";

// Supprimer l'ancien si existe
$db->exec("DELETE FROM cashiers WHERE username = 'test'");

// Insérer le nouveau
$stmt = $db->prepare("INSERT INTO cashiers (username, password, full_name, commission_rate, is_active) VALUES (?, ?, ?, 5, 1)");
$stmt->execute([$username, $hashed, $full_name]);

echo "✅ Nouveau caissier créé:<br>";
echo "Username: <strong>test</strong><br>";
echo "Password: <strong>password123</strong><br>";
echo "Nom: <strong>$full_name</strong><br><br>";

// Vérifier
$verify = $db->prepare("SELECT * FROM cashiers WHERE username = ?");
$verify->execute(['test']);
$user = $verify->fetch();

if ($user && password_verify($password, $user['password'])) {
    echo "<span style='color:green'>✅ Vérification réussie !</span><br>";
    echo "<a href='cashier_login.php'>Aller à la page de connexion</a>";
} else {
    echo "<span style='color:red'>❌ Échec de vérification</span>";
}
?>
