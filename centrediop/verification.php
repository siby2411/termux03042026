<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "🔍 VÉRIFICATION AUTOMATIQUE DU SYSTÈME\n";
echo "======================================\n\n";

// 1. Vérifier les médecins et leurs services
$medecins = $conn->query("
    SELECT u.id, u.prenom, u.nom, u.username, s.name as service
    FROM users u
    JOIN services s ON u.service_id = s.id
    WHERE u.role = 'medecin'
    ORDER BY s.name
")->fetchAll();

echo "👨‍⚕️ MÉDECINS:\n";
foreach ($medecins as $m) {
    echo "   Dr. {$m['prenom']} {$m['nom']} ({$m['username']}) - {$m['service']}\n";
}

// 2. Vérifier les patients par service
echo "\n👤 PATIENTS PAR SERVICE:\n";
$services = $conn->query("SELECT id, name FROM services ORDER BY name")->fetchAll();
foreach ($services as $s) {
    $patients = $conn->prepare("
        SELECT p.prenom, p.nom, p.code_patient_unique, f.token
        FROM patients p
        JOIN file_attente f ON p.id = f.patient_id
        WHERE f.service_id = ? AND f.statut = 'en_attente'
        ORDER BY f.cree_a
    ");
    $patients->execute([$s['id']]);
    $liste = $patients->fetchAll();
    
    if (!empty($liste)) {
        echo "\n🏥 {$s['name']}:\n";
        foreach ($liste as $p) {
            echo "   • {$p['prenom']} {$p['nom']} - Code: {$p['code_patient_unique']} - Token: {$p['token']}\n";
        }
    }
}

// 3. Vérifier les identifiants de connexion
echo "\n🔑 IDENTIFIANTS DE CONNEXION:\n";
$users = $conn->query("SELECT username, role, prenom, nom FROM users WHERE role IN ('medecin', 'caissier') ORDER BY role")->fetchAll();
foreach ($users as $u) {
    echo "   {$u['role']}: {$u['username']} - {$u['prenom']} {$u['nom']}\n";
}
?>
