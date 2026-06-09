<?php
require_once __DIR__ . '/../config/database.php'; // connexion $conn
require_once __DIR__ . '/../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Vérifier la table passée en GET
$table = $_GET['table'] ?? '';
$allowedTables = ['VENTILATION_ITEMS', 'ECRITURES_COMPTABLES', 'PLAN_COMPTABLE_UEMOA'];
if(!in_array($table, $allowedTables)) {
    die("Table non autorisée pour l'export.");
}

// Récupérer les données
$stmt = $conn->query("SELECT * FROM `$table`");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Créer le spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Entêtes
if(!empty($rows)) {
    $cols = array_keys($rows[0]);
    foreach($cols as $index => $colName){
        $sheet->setCellValueByColumnAndRow($index + 1, 1, $colName);
    }
}

// Remplir les données
foreach($rows as $rowIndex => $row){
    foreach($cols as $colIndex => $colName){
        $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $row[$colName]);
    }
}

// Envoyer le fichier au navigateur
$filename = $table.'_'.date('Ymd_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>

