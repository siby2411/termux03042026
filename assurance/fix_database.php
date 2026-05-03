<?php
require_once 'config/db.php';

$db = getDB();

// Ajouter la colonne formule si elle n'existe pas
try {
    $db->exec("ALTER TABLE contrats ADD COLUMN formule VARCHAR(50) DEFAULT 'Tiers' AFTER type_contrat");
    echo "✅ Colonne 'formule' ajoutée avec succès\n";
} catch(PDOException $e) {
    echo "ℹ️ La colonne 'formule' existe déjà ou erreur: " . $e->getMessage() . "\n";
}

// Ajouter d'autres colonnes manquantes potentielles
try {
    $db->exec("ALTER TABLE contrats ADD COLUMN type_contrat VARCHAR(50) DEFAULT 'Auto'");
    echo "✅ Colonne 'type_contrat' ajoutée\n";
} catch(PDOException $e) {}

// Mettre à jour les contrats existants avec une formule par défaut
$db->exec("UPDATE contrats SET formule = 'Tiers' WHERE formule IS NULL");
echo "✅ Contrats mis à jour avec la formule par défaut\n";

echo "\n=== Structure de la table contrats ===\n";
$columns = $db->query("DESCRIBE contrats")->fetchAll(PDO::FETCH_ASSOC);
foreach($columns as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
