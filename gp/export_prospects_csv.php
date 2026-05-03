<?php
require_once 'auth.php';
require_once 'db_connect.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=prospects_senegalais.csv');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID','Civilité','Nom','Prénom','Fonction','Association/Entreprise','Email','Téléphone','Adresse','Code postal','Ville','Région','Type','Notes','Date ajout']);
$rows = $pdo->query("SELECT * FROM prospects_senegalais ORDER BY nom")->fetchAll();
foreach ($rows as $r) fputcsv($output, $r);
fclose($output);
exit;
