<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$product_id = $_POST['product_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;
if(isset($_SESSION['cart'][$product_id])) $_SESSION['cart'][$product_id]['quantity'] += $quantity;
else $_SESSION['cart'][$product_id] = ['id' => $product_id, 'quantity' => $quantity];
echo json_encode(['success' => true]);
?>
