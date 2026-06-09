<?php
session_start();
if (!isset($_SESSION['user_id'])) { echo json_encode([]); exit; }
require_once dirname(__DIR__) . '/config/config.php';

$contrat_id = (int)$_GET['contrat_id'];
$stmt = $pdo->prepare("SELECT date_regul, type_regul, reference_piece, montant, description FROM REGULARISATIONS_CONTRATS WHERE contrat_id = ? ORDER BY date_regul DESC");
$stmt->execute([$contrat_id]);
echo json_encode($stmt->fetchAll());
?>
