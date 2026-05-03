<?php
require_once 'config/config.php';
$db = getDB();

echo "<h2>Réinitialisation des mots de passe des caissiers</h2>";

// Nouveau mot de passe
$new_password = 'password123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

echo "Mot de passe: <strong>$new_password</strong><br>";
echo "Hash généré: <code>$hashed_password</code><br><br>";

// Mettre à jour tous les caissiers
$stmt = $db->prepare("UPDATE cashiers SET password = ? WHERE username IN ('fatou', 'mamadou', 'aminata', 'ibrahima')");
$stmt->execute([$hashed_password]);

echo "✅ Mise à jour effectuée<br><br>";

// Vérifier les mises à jour
$cashiers = $db->query("SELECT id, username, full_name, password FROM cashiers")->fetchAll();

echo "<h3>Vérification:</h3>";
foreach($cashiers as $c) {
    $verify = password_verify($new_password, $c['password']);
    $status = $verify ? '✅ OK' : '❌ KO';
    echo "ID: {$c['id']}, Username: {$c['username']}, Nom: {$c['full_name']} - Vérification: $status<br>";
}

echo "<br><a href='cashier_login.php'>Aller à la page de connexion</a>";
?>
