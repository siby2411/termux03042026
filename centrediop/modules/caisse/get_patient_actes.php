<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    if(!isset($_POST['patient_id'])) {
        echo json_encode([]);
        exit;
    }

    $query = "SELECT DISTINCT a.*, s.nom_service 
              FROM actes_medicaux a 
              LEFT JOIN services s ON a.service_id = s.id
              WHERE a.id IN (
                  SELECT DISTINCT acte_id 
                  FROM consultation_actes ca
                  JOIN consultations c ON ca.consultation_id = c.id
                  WHERE c.patient_id = :patient_id
              )
              ORDER BY a.libelle";

    $stmt = $db->prepare($query);
    $stmt->execute([':patient_id' => $_POST['patient_id']]);
    
    $actes = $stmt->fetchAll();
    
    if(empty($actes)) {
        $query_all = "SELECT a.*, s.nom_service 
                      FROM actes_medicaux a 
                      LEFT JOIN services s ON a.service_id = s.id
                      ORDER BY a.libelle LIMIT 20";
        $stmt_all = $db->prepare($query_all);
        $stmt_all->execute();
        $actes = $stmt_all->fetchAll();
    }
    
    echo json_encode($actes);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
