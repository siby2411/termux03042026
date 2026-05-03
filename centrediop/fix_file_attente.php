<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$service_id = 1; // Service Pédiatrie

echo "=== CRÉATION DE LA FILE D'ATTENTE ===\n\n";

// Vider la file d'attente existante
$db->exec("DELETE FROM file_attente");
echo "✅ File d'attente vidée\n";

// Fonction pour générer un token unique
function generateToken($patient_id) {
    return 'TK' . date('Ymd') . str_pad($patient_id, 4, '0', STR_PAD_LEFT);
}

// Ajouter Siby Momo (patient 1)
$stmt = $db->prepare("
    INSERT INTO file_attente (patient_id, service_id, token, statut, priorite, cree_a)
    VALUES (?, ?, ?, 'en_attente', 'normal', NOW())
");

try {
    $stmt->execute([1, $service_id, generateToken(1)]);
    echo "✅ Siby Momo ajouté à la file d'attente (Token: " . generateToken(1) . ")\n";
} catch (Exception $e) {
    echo "❌ Erreur Siby Momo: " . $e->getMessage() . "\n";
}

// Ajouter Fall Aminata (patient 2)
try {
    $stmt->execute([2, $service_id, generateToken(2)]);
    echo "✅ Fall Aminata ajouté à la file d'attente (Token: " . generateToken(2) . ")\n";
} catch (Exception $e) {
    echo "❌ Erreur Fall Aminata: " . $e->getMessage() . "\n";
}

// Vérifier
$result = $db->query("SELECT COUNT(*) FROM file_attente");
$count = $result->fetchColumn();
echo "\n📊 Total en file d'attente: $count patients\n";

// Afficher la file d'attente
$stmt = $db->query("
    SELECT f.*, p.nom, p.prenom, p.code_patient_unique
    FROM file_attente f
    JOIN patients p ON f.patient_id = p.id
    ORDER BY f.cree_a
");
$attente = $stmt->fetchAll();

echo "\n=== FILE D'ATTENTE ACTUELLE ===\n";
foreach ($attente as $a) {
    echo "👤 {$a['prenom']} {$a['nom']} - Code: {$a['code_patient_unique']} - Token: {$a['token']}\n";
}
?>
