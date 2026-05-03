<?php
require_once '../config/database.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_exercice = $data['id_exercice'];

try {
    $pdo->beginTransaction();
    
    // 1. Clôturer l'exercice courant
    $sql = "UPDATE exercices_comptables 
           SET statut_cloture = 'CLOTURE', date_cloture = NOW() 
           WHERE id_exercice = :id_exercice";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_exercice' => $id_exercice]);
    
    // 2. Reporter les soldes
    reporterSoldes($pdo, $id_exercice);
    
    // 3. Créer le nouvel exercice
    $sql_nouveau = "INSERT INTO exercices_comptables 
                   (nom_exercice, date_debut, date_fin, statut_cloture)
                   VALUES (:nom, :debut, :fin, 'OUVERT')";
    
    $date_fin = $pdo->query("SELECT date_fin FROM exercices_comptables 
                            WHERE id_exercice = $id_exercice")->fetchColumn();
    
    $nouvelle_date_debut = date('Y-m-d', strtotime($date_fin . ' +1 day'));
    $nouvelle_date_fin = date('Y-m-d', strtotime($nouvelle_date_debut . ' +1 year -1 day'));
    
    $stmt = $pdo->prepare($sql_nouveau);
    $stmt->execute([
        ':nom' => 'Exercice ' . date('Y'),
        ':debut' => $nouvelle_date_debut,
        ':fin' => $nouvelle_date_fin
    ]);
    
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
