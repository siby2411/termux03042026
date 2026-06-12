<?php
require_once 'config/database.php';
$pdo = getPDO();

// Hachage officiel de '123'
$hash = password_hash('123', PASSWORD_DEFAULT);

// Mise à jour de l'utilisateur
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'secretariat'");
$stmt->execute([$hash]);

if ($stmt->rowCount() > 0) {
    echo "Succès : Le mot de passe de 'secretariat' a été réinitialisé à '123'.";
} else {
    echo "Erreur : Utilisateur 'secretariat' non trouvé. Vérifiez qu'il existe.";
}
?>
