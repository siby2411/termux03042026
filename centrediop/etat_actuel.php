<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== ÉTAT ACTUEL DE LA BASE ===\n\n";

// Patients par service
$patients = $conn->query("
    SELECT s.name as service, 
           COUNT(p.id) as total,
           GROUP_CONCAT(CONCAT(p.prenom, ' ', p.nom, ' (', p.code_patient_unique, ')') SEPARATOR '|') as liste
    FROM patients p
    JOIN file_attente f ON p.id = f.patient_id
    JOIN services s ON f.service_id = s.id
    WHERE f.statut = 'en_attente'
    GROUP BY s.id, s.name
    ORDER BY s.name
")->fetchAll();

foreach ($patients as $p) {
    echo "🏥 {$p['service']} : {$p['total']} patient(s)\n";
    $liste = explode('|', $p['liste']);
    foreach ($liste as $patient) {
        echo "   👤 $patient\n";
    }
    echo "\n";
}

// Médecins par service
echo "\n=== MÉDECINS PAR SERVICE ===\n";
$medecins = $conn->query("
    SELECT s.name as service, 
           GROUP_CONCAT(CONCAT(u.prenom, ' ', u.nom) SEPARATOR ', ') as medecins
    FROM users u
    JOIN services s ON u.service_id = s.id
    WHERE u.role = 'medecin'
    GROUP BY s.id, s.name
    ORDER BY s.name
")->fetchAll();

foreach ($medecins as $m) {
    echo "👨‍⚕️ {$m['service']} : {$m['medecins']}\n";
}

echo "\n=== FILE D'ATTENTE PAR SERVICE ===\n";
$file = $conn->query("
    SELECT s.name as service, f.token, p.prenom, p.nom, p.code_patient_unique
    FROM file_attente f
    JOIN patients p ON f.patient_id = p.id
    JOIN services s ON f.service_id = s.id
    WHERE f.statut = 'en_attente'
    ORDER BY s.name, f.cree_a
")->fetchAll();

foreach ($file as $f) {
    echo "{$f['service']} : {$f['token']} - {$f['prenom']} {$f['nom']} ({$f['code_patient_unique']})\n";
}
?>
