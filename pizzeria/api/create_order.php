<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cashier_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$db = getDB();
$cashier_id = $_SESSION['cashier_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['items']) || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Panier vide']);
    exit;
}

$items = $data['items'];
$total = $data['total'];
$payment_method = $data['payment_method'];
$commission_rate = $data['commission_rate'] ?? 0;
$commission_amount = ($total * $commission_rate) / 100;

try {
    $db->beginTransaction();
    
    $order_number = 'CMD-' . date('YmdHis') . '-' . rand(100, 999);
    
    $sql = "INSERT INTO orders (order_number, cashier_id, subtotal, grand_total, payment_method, payment_status, order_status, order_type) 
            VALUES (:order_number, :cashier_id, :subtotal, :grand_total, :payment_method, 'paid', 'confirmed', 'emporter')";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':order_number' => $order_number,
        ':cashier_id' => $cashier_id,
        ':subtotal' => $total,
        ':grand_total' => $total,
        ':payment_method' => $payment_method
    ]);
    
    $order_id = $db->lastInsertId();
    
    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) 
                 VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price)";
    $stmt_item = $db->prepare($sql_item);
    
    foreach ($items as $item) {
        $stmt_item->execute([
            ':order_id' => $order_id,
            ':product_id' => $item['id'],
            ':quantity' => $item['quantity'],
            ':unit_price' => $item['price'],
            ':total_price' => $item['price'] * $item['quantity']
        ]);
    }
    
    if ($commission_amount > 0) {
        $sql_comm = "INSERT INTO cashier_commissions (cashier_id, order_id, sale_amount, commission_amount, period_month) 
                     VALUES (?, ?, ?, ?, DATE_FORMAT(NOW(), '%Y-%m-01'))";
        $stmt_comm = $db->prepare($sql_comm);
        $stmt_comm->execute([$cashier_id, $order_id, $total, $commission_amount]);
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'order_number' => $order_number,
        'order_id' => $order_id,
        'commission' => $commission_amount,
        'message' => 'Commande enregistrée'
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
