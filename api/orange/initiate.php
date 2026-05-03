<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

$amount = $_POST['amount'] ?? 0;
$phone = $_POST['phone'] ?? '';

$reference = 'ORANGE_' . time() . rand(1000,9999);
$ussd_code = "#144#" . $amount . "*" . $reference;

echo json_encode(['success' => true, 'ussd_code' => $ussd_code, 'reference' => $reference, 'instruction' => "Composez #144# sur votre téléphone"]);
?>
