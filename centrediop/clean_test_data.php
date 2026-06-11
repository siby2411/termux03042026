<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();
    
    // Supprimer les paiements de test
    $conn->exec("DELETE FROM paiements WHERE id > 10");
    
    // Supprimer les consultations de test
    $conn->exec("DELETE FROM consultations WHERE id > 10");
    
    // Supprimer les file_attente de test
    $conn->exec("DELETE FROM file_attente WHERE id > 10");
    
    // Supprimer les dossiers médicaux de test
    $conn->exec("DELETE FROM dossiers_medicaux WHERE id > 10");
    
    // Supprimer les patients de test (garder les vrais)
    $conn->exec("DELETE FROM patients WHERE id > 10");
    
    $conn->commit();
    echo "✅ Données de test nettoyées avec succès\n";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
