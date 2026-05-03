<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$product_id = $_POST['product_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;

$_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;

echo json_encode(['success' => true, 'cart_count' => array_sum($_SESSION['cart'])]);
?>
