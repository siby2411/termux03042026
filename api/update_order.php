<?php
require_once '../config/config.php';
$db = getDB();

$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';

$stmt = $db->prepare("UPDATE orders SET order_status=? WHERE id=?");
$stmt->execute([$status, $id]);

echo json_encode(['success' => true]);
?>
