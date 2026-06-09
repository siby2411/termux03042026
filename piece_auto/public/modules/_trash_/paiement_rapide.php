<?php
session_start();
if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Non authentifié']); exit; }
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/config.php';

$id = (int)$_GET['id'];

try {
    $echeance = $pdo->prepare("SELECT * FROM ECHEANCIERS_PAIEMENT WHERE id = ?");
    $echeance->execute([$id]);
    $e = $echeance->fetch();
    
    if($e) {
        $update = $pdo->prepare("UPDATE ECHEANCIERS_PAIEMENT SET statut = 'REGLE', date_reglement = CURDATE(), montant_regle = montant WHERE id = ?");
        $update->execute([$id]);
        
        // Création de l'écriture comptable
        if($e['type_echeance'] == 'CLIENT') {
            $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (CURDATE(), ?, 521, 411, ?, ?, 'REGLEMENT')");
            $stmt->execute([$e['libelle'], $e['montant'], $e['reference_facture']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (CURDATE(), ?, 401, 521, ?, ?, 'REGLEMENT')");
            $stmt->execute([$e['libelle'], $e['montant'], $e['reference_facture']]);
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Échéance non trouvée']);
    }
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
