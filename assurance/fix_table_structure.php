<?php
require_once 'config/db.php';

$db = getDB();

// Modifier la structure de la table clients pour accepter NULL
try {
    $db->exec("ALTER TABLE clients MODIFY nom VARCHAR(100) NULL");
    $db->exec("ALTER TABLE clients MODIFY prenom VARCHAR(100) NULL");
    $db->exec("ALTER TABLE clients MODIFY raison_sociale VARCHAR(200) NULL");
    $db->exec("ALTER TABLE clients MODIFY email VARCHAR(100) NULL");
    $db->exec("ALTER TABLE clients MODIFY date_naissance DATE NULL");
    $db->exec("ALTER TABLE clients MODIFY piece_identite ENUM('CNI', 'Passeport', 'Permis', 'Autre') NULL");
    $db->exec("ALTER TABLE clients MODIFY num_piece VARCHAR(50) NULL");
    echo "✅ Structure de la table clients modifiée\n";
} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}

// Ajouter une contrainte de vérification
try {
    $db->exec("ALTER TABLE clients ADD CONSTRAINT chk_client_type CHECK (
        (type_client = 'particulier' AND nom IS NOT NULL AND prenom IS NOT NULL) OR
        (type_client = 'entreprise' AND raison_sociale IS NOT NULL)
    )");
    echo "✅ Contrainte ajoutée\n";
} catch(PDOException $e) {
    echo "Contrainte existante ou erreur: " . $e->getMessage() . "\n";
}

echo "\nStructure corrigée avec succès !\n";
?>
