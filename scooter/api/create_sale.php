<?php
require_once '../config/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$db = getDB();

try {
    $db->beginTransaction();
    $sale_number = 'SALE-' . date('Ymd') . '-' . rand(1000, 9999);
    $stmt = $db->prepare("INSERT INTO sales (sale_number, customer_id, subtotal, grand_total, payment_method, sale_type) VALUES (?, ?, ?, ?, ?, 'sale')");
    $stmt->execute([$sale_number, $data['customer_id'] ?: null, $data['total'], $data['total'], $data['payment_method']]);
    $sale_id = $db->lastInsertId();
    foreach($data['items'] as $item) {
        $stmt = $db->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sale_id, $item['id'], $item['quantity'], $item['price'], $item['price'] * $item['quantity']]);
        $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['id']]);
    }
    $db->commit();
    echo json_encode(['success' => true, 'sale_number' => $sale_number]);
} catch(Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
