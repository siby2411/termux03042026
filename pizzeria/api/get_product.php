<?php
require_once '../config/config.php';
header('Content-Type: application/json');

$db = getDB();
$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'product' => $product]);
?>
