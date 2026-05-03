<?php
require_once '../config/config.php';
$db = getDB();
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'add':
        $code = generateCode('PRD');
        $stmt = $db->prepare("INSERT INTO products (product_code, product_name, description, category_id, brand_id, unit_price, stock_quantity, discount_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$code, $_POST['product_name'], $_POST['description'] ?? '', $_POST['category_id'] ?: null, $_POST['brand_id'] ?: null, $_POST['unit_price'], $_POST['stock_quantity'] ?? 0, $_POST['discount_percentage'] ?? 0]);
        echo json_encode(['success' => $success]);
        break;
    case 'edit':
        $stmt = $db->prepare("UPDATE products SET product_name=?, description=?, category_id=?, brand_id=?, unit_price=?, stock_quantity=?, discount_percentage=? WHERE id=?");
        $success = $stmt->execute([$_POST['product_name'], $_POST['description'] ?? '', $_POST['category_id'] ?: null, $_POST['brand_id'] ?: null, $_POST['unit_price'], $_POST['stock_quantity'] ?? 0, $_POST['discount_percentage'] ?? 0, $_POST['id']]);
        echo json_encode(['success' => $success]);
        break;
    case 'delete':
        $stmt = $db->prepare("DELETE FROM products WHERE id=?");
        $success = $stmt->execute([$_GET['id']]);
        echo json_encode(['success' => $success]);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
}
?>
