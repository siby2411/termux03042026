<?php
$conn = new mysqli('localhost','root','','omega_multisectoriel');

$r = $conn->query("SELECT COUNT(*) c FROM depenses_details")->fetch_assoc()['c'];

echo "Transactions live: ".$r." | ".date('H:i:s');
?>
