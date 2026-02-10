<?php
session_start();
require __DIR__ . '/../includes/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$stmt = $pdo->query("
SELECT e.ecriture_id, s.nom_societe, e.date_operation, e.libelle_operation,
pd.intitule_compte AS compte_debite, pc.intitule_compte AS compte_credite, e.montant
FROM ECRITURES_COMPTABLES e
JOIN SOCIETES s ON e.societe_id = s.societe_id
JOIN PLAN_COMPTABLE_UEMOA pd ON e.compte_debite_id = pd.compte_id
JOIN PLAN_COMPTABLE_UEMOA pc ON e.compte_credite_id = pc.compte_id
ORDER BY e.date_operation
");
$ecritures = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Société');
$sheet->setCellValue('C1', 'Date');
$sheet->setCellValue('D1', 'Libellé');
$sheet->setCellValue('E1', 'Compte Débit');
$sheet->setCellValue('F1', 'Compte Crédit');
$sheet->setCellValue('G1', 'Montant');

$row = 2;
foreach($ecritures as $e){
    $sheet->setCellValue('A'.$row, $e['ecriture_id']);
    $sheet->setCellValue('B'.$row, $e['nom_societe']);
    $sheet->setCellValue('C'.$row, $e['date_operation']);
    $sheet->setCellValue('D'.$row, $e['libelle_operation']);
    $sheet->setCellValue('E'.$row, $e['compte_debite']);
    $sheet->setCellValue('F'.$row, $e['compte_credite']);
    $sheet->setCellValue('G'.$row, $e['montant']);
    $row++;
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Ecritures.xlsx"');
$writer->save('php://output');
exit;

