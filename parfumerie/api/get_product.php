<?php
require_once '../config/config.php';
$db = getDB();
header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'product' => $product]);
?>
