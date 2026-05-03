#!/usr/bin/env php
<?php
echo "🔧 RÉPARATION COMPLÈTE DU DASHBOARD DE DR. FALL\n";
echo "==============================================\n\n";

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$medecin_id = 4;
$service_id = 1;
$today = date('Y-m-d');

// 1. Vérifier/ajouter le service du médecin
echo "1️⃣ VÉRIFICATION DU SERVICE DU MÉDECIN\n";
$stmt = $db->prepare("UPDATE users SET service_id = ? WHERE id = ? AND (service_id IS NULL OR service_id = 0)");
$stmt->execute([$service_id, $medecin_id]);
echo "✅ Service ID 1 affecté à Dr. Fall\n\n";

// 2. Vider et recréer les rendez-vous
echo "2️⃣ CRÉATION DES RENDEZ-VOUS\n";
$db->prepare("DELETE FROM rendez_vous WHERE medecin_id = ? AND date_rdv = ?")->execute([$medecin_id, $today]);

$rdv_sql = "INSERT INTO rendez_vous (patient_id, medecin_id, service_id, date_rdv, heure_rdv, motif, statut) VALUES (?, ?, ?, ?, ?, ?, ?)";
$rdv_stmt = $db->prepare($rdv_sql);

$rdv_stmt->execute([1, $medecin_id, $service_id, $today, '09:00:00', 'Consultation de suivi', 'confirme']);
echo "✅ Rendez-vous Siby Momo 09:00 créé\n";

$rdv_stmt->execute([2, $medecin_id, $service_id, $today, '10:30:00', 'Première consultation', 'programme']);
echo "✅ Rendez-vous Fall Aminata 10:30 créé\n\n";

// 3. Vider et recréer la file d'attente
echo "3️⃣ CRÉATION DE LA FILE D'ATTENTE\n";
$db->exec("DELETE FROM file_attente");

function generateToken($patient_id) {
    return 'TK' . date('Ymd') . str_pad($patient_id, 4, '0', STR_PAD_LEFT);
}

$file_sql = "INSERT INTO file_attente (patient_id, service_id, token, statut, priorite) VALUES (?, ?, ?, 'en_attente', 'normal')";
$file_stmt = $db->prepare($file_sql);

$file_stmt->execute([1, $service_id, generateToken(1)]);
echo "✅ Siby Momo ajouté (Token: " . generateToken(1) . ")\n";

$file_stmt->execute([2, $service_id, generateToken(2)]);
echo "✅ Fall Aminata ajouté (Token: " . generateToken(2) . ")\n\n";

// 4. Vérification finale
echo "4️⃣ VÉRIFICATION FINALE\n";
$rdv = $db->query("SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = $medecin_id AND date_rdv = CURDATE()")->fetchColumn();
$file = $db->query("SELECT COUNT(*) FROM file_attente")->fetchColumn();

echo "✅ Rendez-vous aujourd'hui: $rdv\n";
echo "✅ File d'attente: $file\n\n";

echo "🎉 TOUT EST RÉPARÉ ! Connectez-vous maintenant avec dr.fall / pediatre123\n";
?>
