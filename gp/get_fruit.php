<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID invalide']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM fruits_tropicaux WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success' => false, 'error' => 'Produit non trouvé']);
    exit;
}

echo json_encode(['success' => true, 'product' => $product]);
?>
