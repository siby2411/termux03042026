<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="statistiques_centre_sante.csv"');

$output = fopen('php://output', 'w');

// En-tête du CSV
fputcsv($output, ['Service', 'Nombre de Consultations', 'Nombre de Traitements', 'Urgences']);

// Récupérer les statistiques
$stats = getConsultationStatsByService($pdo);

foreach ($stats as $row) {
    fputcsv($output, $row);
}

fclose($output);
