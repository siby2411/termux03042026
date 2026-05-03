<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$medecin_id = 4;

// Vérifier les infos du médecin
$stmt = $db->prepare("
    SELECT u.*, s.name as service_nom, s.id as service_id
    FROM users u
    LEFT JOIN services s ON u.service_id = s.id
    WHERE u.id = ?
");
$stmt->execute([$medecin_id]);
$medecin = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== INFORMATIONS DR. FALL ===\n\n";
echo "ID: " . $medecin['id'] . "\n";
echo "Nom: " . $medecin['prenom'] . " " . $medecin['nom'] . "\n";
echo "Service ID: " . ($medecin['service_id'] ?? 'Non défini') . "\n";
echo "Service Nom: " . ($medecin['service_nom'] ?? 'Non défini') . "\n";

// Si pas de service, affecter au service 1
if (!$medecin['service_id']) {
    echo "\n⚠️ Service non défini, correction...\n";
    $update = $db->prepare("UPDATE users SET service_id = 1 WHERE id = ?");
    $update->execute([$medecin_id]);
    echo "✅ Service ID 1 (Pédiatrie) affecté à Dr. Fall\n";
}

// Vérifier tous les services disponibles
echo "\n=== SERVICES DISPONIBLES ===\n";
$services = $db->query("SELECT id, name FROM services");
while ($s = $services->fetch()) {
    echo "ID {$s['id']}: {$s['name']}\n";
}
?>
