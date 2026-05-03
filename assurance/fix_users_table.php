<?php
require_once 'config/db.php';

$db = getDB();

// Ajouter la colonne dernier_connexion si elle n'existe pas
try {
    $db->exec("ALTER TABLE utilisateurs ADD COLUMN dernier_connexion TIMESTAMP NULL");
    echo "✅ Colonne 'dernier_connexion' ajoutée\n";
} catch(PDOException $e) {
    echo "ℹ️ Colonne existe déjà ou erreur: " . $e->getMessage() . "\n";
}

// Ajouter la colonne email si elle n'existe pas
try {
    $db->exec("ALTER TABLE utilisateurs ADD COLUMN email VARCHAR(100) NULL");
    echo "✅ Colonne 'email' ajoutée\n";
} catch(PDOException $e) {}

// Vérifier et créer l'utilisateur admin
$check = $db->query("SELECT * FROM utilisateurs WHERE nom_utilisateur = 'admin'");
if(!$check->fetch()) {
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe, nom, prenom, role, actif) 
               VALUES ('admin', '$password_hash', 'Administrateur', 'Système', 'admin', 1)");
    echo "✅ Utilisateur admin créé\n";
} else {
    echo "✓ Utilisateur admin existe déjà\n";
}

echo "\n=== UTILISATEURS DISPONIBLES ===\n";
$users = $db->query("SELECT id, nom_utilisateur, nom, prenom, role FROM utilisateurs WHERE actif=1");
while($u = $users->fetch()) {
    echo "- " . $u['nom_utilisateur'] . " (" . $u['role'] . ")\n";
}
?>
