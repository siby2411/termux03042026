<?php
$conn = new mysqli('localhost','root','','omega_multisectoriel');

$alerts = [];

$r = $conn->query("SELECT COUNT(*) nb FROM incidents WHERE gravite='Critique'")->fetch_assoc()['nb'];
if ($r > 0) $alerts[] = "Incidents critiques détectés: $r";

$r2 = $conn->query("SELECT COUNT(*) nb FROM reserves_qualite WHERE criticite='Critique' AND statut!='Valide'")->fetch_assoc()['nb'];
if ($r2 > 0) $alerts[] = "Réserves critiques en attente: $r2";

$r3 = $conn->query("
SELECT COUNT(*) nb FROM (
SELECT composante_id FROM depenses_details
GROUP BY composante_id
HAVING SUM(montant_reel) > SUM(montant_prevu)
) t
")->fetch_assoc()['nb'];

if ($r3 > 0) $alerts[] = "Dépassement budgétaire détecté: $r3 composantes";

?>
