<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$medecin_id = 4; // ID de Dr. Fall

echo "=== RENDEZ-VOUS DE DR. FALL ===\n\n";

// Rendez-vous aujourd'hui
$stmt = $db->prepare("
    SELECT r.*, p.nom, p.prenom, p.code_patient_unique, p.telephone
    FROM rendez_vous r
    JOIN patients p ON r.patient_id = p.id
    WHERE r.medecin_id = ? AND r.date_rdv = CURDATE()
    ORDER BY r.heure_rdv
");
$stmt->execute([$medecin_id]);
$rdv = $stmt->fetchAll();

if (empty($rdv)) {
    echo "Aucun rendez-vous aujourd'hui pour Dr. Fall\n";
    
    // Créer des rendez-vous de test
    echo "\nCréation de rendez-vous de test...\n";
    
    $insert = $db->prepare("
        INSERT INTO rendez_vous (patient_id, medecin_id, service_id, date_rdv, heure_rdv, motif, statut)
        VALUES (?, ?, 1, CURDATE(), ?, ?, ?)
    ");
    
    // Rendez-vous pour Siby Momo (patient 1) à 09:00
    $insert->execute([1, $medecin_id, '09:00:00', 'Consultation de suivi', 'confirme']);
    echo "✅ Rendez-vous créé pour Siby Momo à 09:00\n";
    
    // Rendez-vous pour Fall Aminata (patient 2) à 10:30
    $insert->execute([2, $medecin_id, '10:30:00', 'Première consultation', 'programme']);
    echo "✅ Rendez-vous créé pour Fall Aminata à 10:30\n";
    
} else {
    foreach ($rdv as $r) {
        echo "⏰ " . substr($r['heure_rdv'], 0, 5) . " - " . $r['prenom'] . " " . $r['nom'] . " (" . $r['code_patient_unique'] . ")\n";
    }
}

// File d'attente
$stmt = $db->prepare("
    SELECT f.*, p.nom, p.prenom, p.code_patient_unique
    FROM file_attente f
    JOIN patients p ON f.patient_id = p.id
    WHERE f.service_id = (SELECT service_id FROM users WHERE id = ?)
    AND f.statut = 'en_attente'
");
$stmt->execute([$medecin_id]);
$attente = $stmt->fetchAll();

echo "\n=== FILE D'ATTENTE ===\n";
if (empty($attente)) {
    echo "Aucun patient en file d'attente\n";
    
    // Ajouter à la file d'attente
    $insert = $db->prepare("
        INSERT INTO file_attente (patient_id, service_id, token, statut, priorite, cree_a)
        VALUES (?, 1, ?, 'en_attente', 'normal', NOW())
    ");
    
    $insert->execute([1, 'TK' . date('Ymd') . '0001']);
    $insert->execute([2, 'TK' . date('Ymd') . '0002']);
    echo "✅ Patients ajoutés à la file d'attente\n";
} else {
    foreach ($attente as $a) {
        echo "👤 " . $a['prenom'] . " " . $a['nom'] . " (" . $a['code_patient_unique'] . ") - Token: " . $a['token'] . "\n";
    }
}
?>
