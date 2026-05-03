<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$service_id = $_GET['service_id'] ?? 0;

if ($service_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Service non spécifié']);
    exit();
}

try {
    $query = "SELECT s.numero_salle, s.etage, s.statut,
                     b.nom as batiment_nom,
                     (SELECT COUNT(*) FROM materiel WHERE salle_id = s.id) as nb_materiel
              FROM salles s
              JOIN batiments b ON s.batiment_id = b.id
              WHERE s.service_id = :service_id
              ORDER BY s.etage, s.numero_salle";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':service_id' => $service_id]);
    $salles = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'salles' => $salles]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
