<?php
$conn = new mysqli("localhost","root","","omega_multisectoriel");

header("Content-Type: application/json");

$data = $conn->query("
SELECT 
SUM(montant_reel) spent,
SUM(montant_prevu) budget
FROM depenses_details
")->fetch_assoc();

echo json_encode([
"spent"=>$data['spent'],
"budget"=>$data['budget'],
"timestamp"=>date("H:i:s")
]);
?>
