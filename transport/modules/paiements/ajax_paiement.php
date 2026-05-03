<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

if($_POST['action'] == 'add') {
    $reference = $_POST['reference_transaction'] ?: 'REF-' . date('YmdHis');
    $query = "INSERT INTO paiements (id_eleve, montant, mois_periode, mode_paiement, reference_transaction, statut_paiement, date_paiement) 
              VALUES (?, ?, ?, ?, ?, 'paye', NOW())";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        $_POST['id_eleve'], $_POST['montant'], $_POST['mois_periode'] . '-01',
        $_POST['mode_paiement'], $reference
    ]);
    echo json_encode(['success' => $result]);
}
?>
