<?php
require_once '../config/config.php';
$db = getDB();
header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM customers WHERE id=?");
$stmt->execute([$id]);
echo json_encode(['success' => true, 'customer' => $stmt->fetch(PDO::FETCH_ASSOC)]);
?>
