<?php
require_once '../config/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$db = getDB();

$repair_number = 'REP-' . date('Ymd') . '-' . rand(1000, 9999);
$stmt = $db->prepare("INSERT INTO repairs (repair_number, customer_name, customer_phone, scooter_brand, scooter_model, problem_description, estimated_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
$success = $stmt->execute([$repair_number, $data['customer_name'], $data['customer_phone'], $data['scooter_brand'], $data['scooter_model'], $data['problem_description'], $data['estimated_cost']]);

echo json_encode(['success' => $success, 'repair_number' => $repair_number]);
?>
