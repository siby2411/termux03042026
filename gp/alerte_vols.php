<?php
require_once 'db_connect.php';
require_once 'api_whatsapp.php';
$vols = $pdo->query("SELECT v.*, c.telephone, c.nom FROM vols v JOIN clients c ON c.type IN ('expediteur','both') WHERE v.date_depart BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 DAY)")->fetchAll();
foreach ($vols as $vol) {
    $message = "🚨 Nouveau vol {$vol['numero_vol']} de {$vol['depart_ville']} vers {$vol['arrivee_ville']} le {$vol['date_depart']}. Réservez votre espace !";
    envoyer_whatsapp($vol['telephone'], $message);
}
?>
