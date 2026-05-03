<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $db->beginTransaction();

    $check_query = "SELECT id FROM paiements WHERE numero_facture = :numero_facture";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([':numero_facture' => $_POST['numero_facture']]);
    
    if($check_stmt->fetch()) {
        $_POST['numero_facture'] = 'FAC-' . date('ymd') . '-' . rand(1000, 9999);
    }

    $query = "INSERT INTO paiements (
                numero_facture, 
                patient_id, 
                caissier_id, 
                montant_total, 
                montant_paye, 
                montant_restant, 
                mode_paiement, 
                statut, 
                observations,
                date_paiement
              ) VALUES (
                :numero_facture,
                :patient_id,
                :caissier_id,
                :montant,
                :montant,
                0,
                :mode_paiement,
                'paye',
                :observations,
                NOW()
              )";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        ':numero_facture' => $_POST['numero_facture'],
        ':patient_id' => $_POST['patient_id'],
        ':caissier_id' => $_POST['caissier_id'],
        ':montant' => $_POST['montant'],
        ':mode_paiement' => $_POST['mode_paiement'],
        ':observations' => $_POST['observations'] ?? ''
    ]);

    if($result) {
        $update_queue = "UPDATE file_attente SET statut = 'termine' 
                        WHERE patient_id = :patient_id AND statut = 'en_attente'";
        $stmt_queue = $db->prepare($update_queue);
        $stmt_queue->execute([':patient_id' => $_POST['patient_id']]);

        $db->commit();
        $response['success'] = true;
        $response['message'] = 'Paiement enregistré avec succès';
        $response['numero_facture'] = $_POST['numero_facture'];
    } else {
        $db->rollBack();
        $response['message'] = 'Erreur lors de l\'enregistrement';
    }

} catch(Exception $e) {
    if(isset($db)) {
        $db->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
