<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'caissier') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$code_patient = $_POST['code_patient'] ?? '';

if (empty($code_patient)) {
    echo json_encode(['success' => false, 'message' => 'Code patient requis']);
    exit();
}

try {
    // Rechercher le patient par son code
    $query_patient = "SELECT id, prenom, nom, code_patient_unique, telephone 
                      FROM patients 
                      WHERE code_patient_unique = :code 
                         OR numero_patient = :code 
                      LIMIT 1";
    $stmt_patient = $db->prepare($query_patient);
    $stmt_patient->execute([':code' => $code_patient]);
    $patient = $stmt_patient->fetch();
    
    if (!$patient) {
        echo json_encode(['success' => false, 'message' => 'Patient non trouvé']);
        exit();
    }
    
    // Récupérer les traitements prévus pour ce patient
    $query_traitements = "SELECT DISTINCT 
                          a.id as acte_id,
                          a.libelle as traitement_nom,
                          a.prix_traitement as montant,
                          s.name as service_nom,
                          rv.date_rdv,
                          rv.heure_rdv,
                          u.nom as medecin_nom,
                          u.prenom as medecin_prenom,
                          rv.id as rendez_vous_id,
                          rv.statut as rdv_statut
                          FROM rendez_vous rv
                          JOIN services s ON rv.service_id = s.id
                          JOIN actes_medicaux a ON a.service_id = s.id
                          LEFT JOIN users u ON rv.medecin_id = u.id
                          WHERE rv.patient_id = :patient_id
                          AND a.prix_traitement > 0
                          AND rv.statut IN ('programme', 'confirme')
                          ORDER BY rv.date_rdv DESC, rv.heure_rdv DESC";
    
    $stmt_traitements = $db->prepare($query_traitements);
    $stmt_traitements->execute([':patient_id' => $patient['id']]);
    $traitements = $stmt_traitements->fetchAll();
    
    echo json_encode([
        'success' => true,
        'patient' => [
            'id' => $patient['id'],
            'nom' => $patient['prenom'] . ' ' . $patient['nom'],
            'code' => $patient['code_patient_unique'],
            'telephone' => $patient['telephone']
        ],
        'traitements' => $traitements
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
