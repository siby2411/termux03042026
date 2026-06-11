<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$medecin_id = 4; // Dr. Fall
$service_id = 3; // Pédiatrie
$today = date('Y-m-d');

echo "🔧 RECRÉATION DES RENDEZ-VOUS\n";
echo "============================\n\n";

// Supprimer les anciens rendez-vous d'aujourd'hui
$db->prepare("DELETE FROM rendez_vous WHERE medecin_id = ? AND date_rdv = ?")->execute([$medecin_id, $today]);
echo "✅ Anciens rendez-vous supprimés\n";

// Récupérer les patients
$patients = $db->query("SELECT id, nom, prenom FROM patients ORDER BY id");
$patient_list = $patients->fetchAll();

$rdv_stmt = $db->prepare("
    INSERT INTO rendez_vous (patient_id, medecin_id, service_id, date_rdv, heure_rdv, motif, statut)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

// Rendez-vous à 09:00 pour le premier patient
if (isset($patient_list[0])) {
    $rdv_stmt->execute([
        $patient_list[0]['id'],
        $medecin_id,
        $service_id,
        $today,
        '09:00:00',
        'Consultation de suivi',
        'confirme'
    ]);
    echo "✅ Rendez-vous 09:00 - {$patient_list[0]['prenom']} {$patient_list[0]['nom']}\n";
}

// Rendez-vous à 10:30 pour le deuxième patient
if (isset($patient_list[1])) {
    $rdv_stmt->execute([
        $patient_list[1]['id'],
        $medecin_id,
        $service_id,
        $today,
        '10:30:00',
        'Première consultation',
        'programme'
    ]);
    echo "✅ Rendez-vous 10:30 - {$patient_list[1]['prenom']} {$patient_list[1]['nom']}\n";
}

// Vérification
$rdv_count = $db->prepare("SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = ? AND date_rdv = ?");
$rdv_count->execute([$medecin_id, $today]);
$total = $rdv_count->fetchColumn();

echo "\n📊 Total rendez-vous aujourd'hui: $total\n";
?>
