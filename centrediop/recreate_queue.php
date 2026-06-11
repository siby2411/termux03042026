<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "🔧 RECRÉATION DE LA FILE D'ATTENTE\n";
echo "==================================\n\n";

// Vider la file d'attente existante
$db->exec("DELETE FROM file_attente");
echo "✅ Ancienne file d'attente vidée\n";

// Réinitialiser l'auto-increment
$db->exec("ALTER TABLE file_attente AUTO_INCREMENT = 1");

// Vérifier les patients
$patients = $db->query("SELECT id, nom, prenom, code_patient_unique FROM patients ORDER BY id");
$patient_list = $patients->fetchAll();

if (count($patient_list) == 0) {
    echo "❌ Aucun patient trouvé dans la base\n";
    exit;
}

echo "\n📋 Patients trouvés:\n";
foreach ($patient_list as $p) {
    echo "   - ID {$p['id']}: {$p['prenom']} {$p['nom']} ({$p['code_patient_unique']})\n";
}

echo "\n";

// Fonction pour générer un token unique
function generateToken($patient_id) {
    return 'TK' . date('Ymd') . str_pad($patient_id, 4, '0', STR_PAD_LEFT);
}

// Ajouter les patients à la file d'attente
$service_id = 3; // Service Pédiatrie (ID 3 d'après votre check_service.php)
$stmt = $db->prepare("
    INSERT INTO file_attente (patient_id, service_id, token, statut, priorite, cree_a)
    VALUES (?, ?, ?, 'en_attente', 'normal', NOW())
");

foreach ($patient_list as $patient) {
    $token = generateToken($patient['id']);
    try {
        $stmt->execute([$patient['id'], $service_id, $token]);
        echo "✅ {$patient['prenom']} {$patient['nom']} ajouté (Token: $token)\n";
    } catch (Exception $e) {
        echo "❌ Erreur pour {$patient['prenom']} {$patient['nom']}: " . $e->getMessage() . "\n";
    }
}

// Vérification
$count = $db->query("SELECT COUNT(*) FROM file_attente")->fetchColumn();
echo "\n📊 Total en file d'attente: $count patients\n";

// Afficher la file d'attente
$queue = $db->query("
    SELECT f.*, p.nom, p.prenom, p.code_patient_unique
    FROM file_attente f
    JOIN patients p ON f.patient_id = p.id
    ORDER BY f.cree_a
");

echo "\n=== FILE D'ATTENTE ACTUELLE ===\n";
while ($row = $queue->fetch()) {
    echo "   👤 {$row['prenom']} {$row['nom']} - Code: {$row['code_patient_unique']} - Token: {$row['token']}\n";
}
?>
