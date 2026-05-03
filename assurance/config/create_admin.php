<?php
require_once 'db.php';

$db = getDB();

// Supprimer l'ancien utilisateur admin s'il existe
$db->exec("DELETE FROM utilisateurs WHERE nom_utilisateur = 'admin'");

// Créer un nouvel utilisateur admin avec mot de passe hashé
$password_hash = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe, email, nom, prenom, role, actif) 
        VALUES (:username, :password, :email, :nom, :prenom, :role, 1)";

$stmt = $db->prepare($sql);
$result = $stmt->execute([
    ':username' => 'admin',
    ':password' => $password_hash,
    ':email' => 'admin@assurance.sn',
    ':nom' => 'Administrateur',
    ':prenom' => 'Système',
    ':role' => 'admin'
]);

if($result) {
    echo "✅ Utilisateur admin créé avec succès !\n";
    echo "👤 Identifiant: admin\n";
    echo "🔑 Mot de passe: admin123\n";
} else {
    echo "❌ Erreur lors de la création de l'utilisateur\n";
}
?>
