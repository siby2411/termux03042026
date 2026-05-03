<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

$amount = $_POST['amount'] ?? 0;
$phone = $_POST['phone'] ?? '';

// Simulation génération QR Code Wave
$reference = 'WAVE_' . time() . rand(1000,9999);
$qr_data = "https://wave.com/pay?ref=" . $reference . "&amount=" . $amount;

// Génération QR code via Google Charts
$qr_code = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($qr_data);

echo json_encode(['success' => true, 'qr_code' => $qr_code, 'reference' => $reference]);
?>
