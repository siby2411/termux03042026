<?php
$conn = new mysqli("localhost","root","","omega_multisectoriel");

$result = $conn->query("
SELECT libelle,
montant_prevu,
montant_reel,
CASE 
WHEN montant_reel > montant_prevu*1.25 THEN 'FRAUDE'
WHEN montant_reel > montant_prevu THEN 'SURCOUT'
ELSE 'OK'
END as status
FROM depenses_details
");

while($r = $result->fetch_assoc()){
    echo $r['libelle']." - ".$r['status']."<br>";
}
?>
