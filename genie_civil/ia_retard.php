<?php
$conn = new mysqli('localhost','root','','omega_multisectoriel');

$result = [];

$q = $conn->query("
SELECT 
tache,
progression,
DATEDIFF(date_fin, date_debut) AS duree
FROM planning_travaux
");

while($row = $q->fetch_assoc()) {

    $jours_ecoules = max(1, rand(1,10)); // simulation ou remplacement chrono réel
    $attendu = ($jours_ecoules / max(1,$row['duree'])) * 100;

    $retard = $attendu - $row['progression'];

    if($retard > 20){
        $result[] = [
            "tache"=>$row['tache'],
            "niveau"=>"RETARD CRITIQUE",
            "score"=>$retard
        ];
    }
}

echo json_encode($result);
?>
