<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== LISTE DES PATIENTS PAR SERVICE ===\n\n";

$patients = $conn->query("
    SELECT p.id, p.prenom, p.nom, p.code_patient_unique, 
           s.name as service_nom, f.token, f.statut
    FROM patients p
    JOIN file_attente f ON p.id = f.patient_id
    JOIN services s ON f.service_id = s.id
    ORDER BY s.name, p.nom
")->fetchAll();

foreach ($patients as $p) {
    echo "{$p['service_nom']}\n";
    echo "   👤 {$p['prenom']} {$p['nom']}\n";
    echo "   🆔 Code: {$p['code_patient_unique']}\n";
    echo "   🎫 Token: {$p['token']}\n";
    echo "   📊 Statut: {$p['statut']}\n\n";
}
?>
