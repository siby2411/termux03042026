<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$medecin_id = 4; // ID de Dr. Fall
$service_id = 1; // Service Pédiatrie
$today = date('Y-m-d');

echo "=== CRÉATION DES RENDEZ-VOUS POUR DR. FALL ===\n\n";

// Vérifier si des rendez-vous existent déjà
$check = $db->prepare("SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = ? AND date_rdv = ?");
$check->execute([$medecin_id, $today]);
$existing = $check->fetchColumn();

if ($existing > 0) {
    echo "⚠️ Des rendez-vous existent déjà pour aujourd'hui\n";
} else {
    // Rendez-vous pour Siby Momo (patient 1) à 09:00
    $stmt = $db->prepare("
        INSERT INTO rendez_vous (patient_id, medecin_id, service_id, date_rdv, heure_rdv, motif, statut, cree_le)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    try {
        $stmt->execute([1, $medecin_id, $service_id, $today, '09:00:00', 'Consultation de suivi', 'confirme']);
        echo "✅ Rendez-vous créé pour Siby Momo à 09:00\n";
    } catch (Exception $e) {
        echo "❌ Erreur Siby Momo: " . $e->getMessage() . "\n";
    }
    
    // Rendez-vous pour Fall Aminata (patient 2) à 10:30
    try {
        $stmt->execute([2, $medecin_id, $service_id, $today, '10:30:00', 'Première consultation', 'programme']);
        echo "✅ Rendez-vous créé pour Fall Aminata à 10:30\n";
    } catch (Exception $e) {
        echo "❌ Erreur Fall Aminata: " . $e->getMessage() . "\n";
    }
}

// Vérifier les rendez-vous créés
$stmt = $db->prepare("
    SELECT r.*, p.nom, p.prenom, p.code_patient_unique
    FROM rendez_vous r
    JOIN patients p ON r.patient_id = p.id
    WHERE r.medecin_id = ? AND r.date_rdv = ?
    ORDER BY r.heure_rdv
");
$stmt->execute([$medecin_id, $today]);
$rdv = $stmt->fetchAll();

echo "\n=== RENDEZ-VOUS DU JOUR ===\n";
if (count($rdv) > 0) {
    foreach ($rdv as $r) {
        echo "⏰ {$r['heure_rdv']} - {$r['prenom']} {$r['nom']} ({$r['code_patient_unique']}) [{$r['statut']}]\n";
    }
} else {
    echo "Aucun rendez-vous trouvé\n";
}
?>
