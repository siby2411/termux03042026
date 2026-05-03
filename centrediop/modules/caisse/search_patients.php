<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT DISTINCT p.id, p.nom, p.prenom, p.telephone, 
                     rv.date_rdv, rv.service_id,
                     f.statut as file_statut
              FROM patients p 
              LEFT JOIN rendez_vous rv ON p.id = rv.patient_id 
              LEFT JOIN file_attente f ON p.id = f.patient_id AND f.statut = 'en_attente'
              WHERE 1=1";

    $params = [];

    if(!empty($_POST['nom'])) {
        $query .= " AND p.nom LIKE :nom";
        $params[':nom'] = '%' . $_POST['nom'] . '%';
    }

    if(!empty($_POST['prenom'])) {
        $query .= " AND p.prenom LIKE :prenom";
        $params[':prenom'] = '%' . $_POST['prenom'] . '%';
    }

    if(!empty($_POST['telephone'])) {
        $query .= " AND p.telephone LIKE :telephone";
        $params[':telephone'] = '%' . $_POST['telephone'] . '%';
    }

    if(!empty($_POST['date_rdv'])) {
        $query .= " AND rv.date_rdv = :date_rdv";
        $params[':date_rdv'] = $_POST['date_rdv'];
    }

    if(!empty($_POST['service'])) {
        $query .= " AND rv.service_id = :service_id";
        $params[':service_id'] = $_POST['service'];
    }

    $query .= " ORDER BY p.nom, p.prenom LIMIT 50";

    $stmt = $db->prepare($query);
    
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $patients = $stmt->fetchAll();

    echo json_encode($patients);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
