<?php
require_once 'includes/config.php';

// Réinitialiser le mot de passe admin
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE username = 'admin'");
$stmt->execute([$hashed_password]);

if ($stmt->rowCount() > 0) {
    echo "✅ Mot de passe admin réinitialisé avec succès !<br>";
    echo "Utilisateur: admin<br>";
    echo "Mot de passe: admin123<br>";
} else {
    // Si admin n'existe pas, le créer
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password, nom, prenom, role, statut) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $hashed_password, 'Administrateur', 'System', 'admin', 'actif']);
    echo "✅ Utilisateur admin créé avec succès !<br>";
    echo "Utilisateur: admin<br>";
    echo "Mot de passe: admin123<br>";
}

// Afficher tous les utilisateurs
$users = $pdo->query("SELECT id, username, role, statut FROM utilisateurs")->fetchAll();
echo "<br><strong>Utilisateurs existants:</strong><br>";
foreach ($users as $user) {
    echo "- ID: {$user['id']}, Username: {$user['username']}, Rôle: {$user['role']}, Statut: {$user['statut']}<br>";
}
?>
