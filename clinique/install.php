<?php
include 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Connexion à la base de données réussie!<br>";
    
    // Vérification des tables
    $tables = ['departements', 'specialites', 'users', 'personnel', 'patients', 'rendez_vous', 'consultations', 'type_analyses', 'demandes_analyses', 'resultats_analyses', 'factures', 'lignes_facture', 'paiements'];
    
    foreach ($tables as $table) {
        $check = $db->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() > 0) {
            echo "✅ Table '$table' existe<br>";
        } else {
            echo "❌ Table '$table' manquante<br>";
        }
    }
    
} catch(PDOException $exception) {
    echo "Erreur: " . $exception->getMessage();
}
?>
