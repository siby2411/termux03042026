<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "=== RÉINITIALISATION DE LA FILE D'ATTENTE ===\n\n";

// Vider la file d'attente
$db->exec("DELETE FROM file_attente");
echo "✅ File d'attente vidée\n";

// Réinitialiser l'auto-increment
$db->exec("ALTER TABLE file_attente AUTO_INCREMENT = 1");

// Fonction pour générer un token unique
function generateUniqueToken($db, $patient_id) {
    $base = 'TK' . date('Ymd');
    $token = $base . str_pad($patient_id, 4, '0', STR_PAD_LEFT);
    
    // Vérifier si le token existe déjà
    $stmt = $db->prepare("SELECT id FROM file_attente WHERE token = ?");
    $stmt->execute([$token]);
    
    if ($stmt->fetch()) {
        // Si existe, ajouter un suffixe
        $token = $base . str_pad($patient_id, 4, '0', STR_PAD_LEFT) . 'A';
    }
    
    return $token;
}

// Ajouter les patients à la file d'attente
$patients = [1, 2]; // IDs des patients
$service_id = 1; // Service de pédiatrie

foreach ($patients as $patient_id) {
    $token = generateUniqueToken($db, $patient_id);
    
    $stmt = $db->prepare("
        INSERT INTO file_attente (patient_id, service_id, token, statut, priorite, cree_a)
        VALUES (?, ?, ?, 'en_attente', 'normal', NOW())
    ");
    
    try {
        $stmt->execute([$patient_id, $service_id, $token]);
        echo "✅ Patient $patient_id ajouté avec token: $token\n";
    } catch (Exception $e) {
        echo "❌ Erreur patient $patient_id: " . $e->getMessage() . "\n";
    }
}

// Vérifier
$result = $db->query("SELECT COUNT(*) FROM file_attente");
$count = $result->fetchColumn();
echo "\n📊 Total en file d'attente: $count patients\n";
?>
