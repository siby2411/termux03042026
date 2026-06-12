<?php
require_once 'config/database.php';
$pdo = getPDO();
$password_hash = password_hash('123', PASSWORD_DEFAULT);

// Vérifier si l'utilisateur existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'secretariat'");
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // Si il existe, on met à jour
    $pdo->prepare("UPDATE users SET password = ?, role = 'secretariat', actif = 1 WHERE username = 'secretariat'")->execute([$password_hash]);
    echo "Utilisateur 'secretariat' mis à jour.";
} else {
    // Sinon on crée
    $pdo->prepare("INSERT INTO users (username, password, role, prenom, nom, actif) VALUES ('secretariat', ?, 'secretariat', 'Agent', 'Secrétariat', 1)")->execute([$password_hash]);
    echo "Utilisateur 'secretariat' créé.";
}
?>
