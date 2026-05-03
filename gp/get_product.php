<?php
require_once 'db_connect.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'negoce';

if ($id <= 0) {
    echo json_encode(['error' => 'ID invalide']);
    exit;
}

if ($type == 'negoce') {
    $stmt = $pdo->prepare("SELECT * FROM negoce WHERE id = ?");
} else {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
}

$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['error' => 'Produit non trouvé']);
    exit;
}

echo json_encode($product);
?>
