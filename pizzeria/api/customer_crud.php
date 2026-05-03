<?php
require_once '../config/config.php';
header('Content-Type: application/json');

$db = getDB();
$action = $_POST['action'] ?? '';

switch($action) {
    case 'add':
        $code = generateCode('CUS');
        $stmt = $db->prepare("INSERT INTO customers (customer_code, first_name, last_name, phone, email, address) VALUES (?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$code, $_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['email'] ?? '', $_POST['address'] ?? '']);
        echo json_encode(['success' => $success]);
        break;
    case 'edit':
        $stmt = $db->prepare("UPDATE customers SET first_name=?, last_name=?, phone=?, email=?, address=? WHERE id=?");
        $success = $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['email'] ?? '', $_POST['address'] ?? '', $_POST['id']]);
        echo json_encode(['success' => $success]);
        break;
    case 'delete':
        $stmt = $db->prepare("DELETE FROM customers WHERE id=?");
        $success = $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => $success]);
        break;
}
?>
