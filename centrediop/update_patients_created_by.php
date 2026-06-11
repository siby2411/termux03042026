<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== MISE À JOUR DES PATIENTS AVEC created_by ===\n\n";

try {
    // Voir combien de patients n'ont pas de created_by
    $count = $conn->query("SELECT COUNT(*) FROM patients WHERE created_by IS NULL")->fetchColumn();
    echo "Patients sans created_by: $count\n";
    
    if ($count > 0) {
        // Option 1: Mettre caissier1 (ID 2) comme créateur par défaut
        $update = $conn->prepare("UPDATE patients SET created_by = 2 WHERE created_by IS NULL");
        $update->execute();
        echo "✅ Patients mis à jour avec created_by = 2 (caissier1)\n";
    }
    
    // Vérifier la répartition
    $repartition = $conn->query("
        SELECT created_by, COUNT(*) as total 
        FROM patients 
        GROUP BY created_by
    ")->fetchAll();
    
    echo "\nRépartition des patients par créateur:\n";
    foreach ($repartition as $r) {
        if ($r['created_by']) {
            $user = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $user->execute([$r['created_by']]);
            $username = $user->fetchColumn();
            echo "   Caissier ID {$r['created_by']} ($username): {$r['total']} patients\n";
        } else {
            echo "   Non assigné: {$r['total']} patients\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
