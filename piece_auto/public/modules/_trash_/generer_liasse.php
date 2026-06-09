<?php
session_start();
if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Non authentifié']); exit; }
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/config.php';

$type = $_GET['type'];
$montant = (float)$_GET['montant'];
$exercice = (int)$_GET['exercice'];

try {
    $stmt = $pdo->prepare("INSERT INTO LIASSES_FISCALES (exercice, type_liasse, date_generation, montant, statut) VALUES (?, ?, CURDATE(), ?, 'GENERE')");
    $stmt->execute([$exercice, $type, $montant]);
    
    echo json_encode(['success' => true, 'message' => 'Déclaration générée']);
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
