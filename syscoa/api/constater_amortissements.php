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
    
    // Logique de calcul et enregistrement des amortissements
    // (à implémenter selon la structure exacte des tables)
    
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
