<?php
require_once 'auth.php';
require_once 'db_connect.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=agenda_rdv.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID','Titre','Date','Heure','Lieu','Type','Contact','Téléphone','Statut','Matériel requis','Notes']);

$rows = $pdo->query("SELECT id, titre, date_rdv, heure_rdv, lieu, type_rdv, contact_nom, contact_telephone, statut, materiel_requis, notes_apres FROM agenda_rdv ORDER BY date_rdv, heure_rdv")->fetchAll();
foreach ($rows as $r) {
    fputcsv($output, $r);
}
fclose($output);
?>
