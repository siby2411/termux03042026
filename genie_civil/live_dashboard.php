<?php
$conn = new mysqli('localhost','root','','omega_multisectoriel');

$data = $conn->query("
SELECT 
SUM(montant_reel) total
FROM depenses_details
")->fetch_assoc();

echo json_encode($data);
?>
