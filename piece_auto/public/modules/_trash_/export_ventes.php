<?php
require_once __DIR__ . '/../../config/Database.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=ventes_omega_tech.csv');

$db = (new Database())->getConnection();
$output = fopen('php://output', 'w');
fputcsv($output, ['ID Vente', 'Date', 'Total Commande', 'Marge Brute']);

$query = $db->query("SELECT id_commande_vente, date_vente, total_commande, marge_brute FROM COMMANDE_VENTE");
while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}
fclose($output);
