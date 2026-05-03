<?php
require_once __DIR__ . '/../config/database.php'; // Connexion PDO $conn
require_once __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../views/sidebar.php';
include __DIR__ . '/../views/topbar.php';

use Mpdf\Mpdf;

$allowedTables = [
    'VENTILATION_ITEMS' => 'Ventilation SYSCOHADA',
    'ECRITURES_COMPTABLES' => 'Écritures Comptables',
    'PLAN_COMPTABLE_UEMOA' => 'Plan Comptable UEMOA'
];

$table = $_GET['table'] ?? '';
if(!$table || !array_key_exists($table, $allowedTables)){
    die("Table non autorisée pour l'export PDF.");
}

// Récupérer les données
$stmt = $conn->query("SELECT * FROM `$table`");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Générer HTML pour PDF
$html = '<h2 class="text-center mb-4">'.$allowedTables[$table].'</h2>';
$html .= '<table class="table table-bordered">';
$html .= '<thead class="table-dark"><tr>';
if(!empty($rows)){
    foreach(array_keys($rows[0]) as $col){
        $html .= "<th>{$col}</th>";
    }
}
$html .= '</tr></thead><tbody>';
foreach($rows as $row){
    $html .= '<tr>';
    foreach($row as $value){
        $html .= "<td>{$value}</td>";
    }
    $html .= '</tr>';
}
$html .= '</tbody></table>';

// Instanciation mPDF
$mpdf = new Mpdf();
$mpdf->WriteHTML('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
$mpdf->WriteHTML($html);
$filename = $table.'_'.date('Ymd_His').'.pdf';
$mpdf->Output($filename,'D');

include __DIR__ . '/../views/footer.php';
?>

