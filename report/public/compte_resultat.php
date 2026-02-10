<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use TCPDF;

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$periode = $_GET['periode'] ?? date('Y-m-d');

// ---------------------------
// Récupération des écritures
// ---------------------------
$stmt = $pdo->prepare("
SELECT e.*, pd.classe, pd.nature_resultat
FROM ECRITURES_COMPTABLES e
JOIN PLAN_COMPTABLE_UEMOA pd
  ON e.compte_debite_id = pd.compte_id OR e.compte_credite_id = pd.compte_id
WHERE e.date_operation <= :periode
");
$stmt->execute(['periode'=>$periode]);
$ecritures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------
// Calcul SIG
// ---------------------------
$chiffre_affaires = 0;
$charges_exploitation = 0;
$produits_financiers = 0;
$charges_financieres = 0;
$resultat_exceptionnel = 0;

foreach($ecritures as $e){
    $classe = $e['classe'];
    $nature = $e['nature_resultat'];
    $montant = (float)$e['montant'];

    if($classe == 7) $chiffre_affaires += $montant;
    if($classe == 6) $charges_exploitation += $montant;
    if($classe == 8 && $nature == 'FIN') $produits_financiers += $montant;
    if($classe == 6 && $nature == 'FIN') $charges_financieres += $montant;
    if($nature == 'HAO') $resultat_exceptionnel += $montant;
}

$ebe = $chiffre_affaires - $charges_exploitation;
$caf = $ebe + $produits_financiers - $charges_financieres;
$resultat_net = $caf + $resultat_exceptionnel;

// ---------------------------
// Export Excel
// ---------------------------
if(isset($_GET['export_excel'])){
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1','Rubrique');
    $sheet->setCellValue('B1','Montant');

    $data = [
        ['Chiffre d\'Affaires', $chiffre_affaires],
        ['Charges Exploitation', $charges_exploitation],
        ['EBE', $ebe],
        ['Produits financiers', $produits_financiers],
        ['Charges financières', $charges_financieres],
        ['CAF', $caf],
        ['Résultat exceptionnel', $resultat_exceptionnel],
        ['Résultat net', $resultat_net],
    ];

    $row = 2;
    foreach($data as $d){
        $sheet->setCellValue("A$row", $d[0]);
        $sheet->setCellValue("B$row", $d[1]);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="compte_resultat.xlsx"');
    $writer->save('php://output');
    exit;
}

// ---------------------------
// Export PDF
// ---------------------------
if(isset($_GET['export_pdf'])){
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

    $html = "<h3>Compte de Résultat au ".htmlspecialchars($periode)."</h3>
    <table border='1' cellpadding='4'>
    <tr><th>Rubrique</th><th>Montant</th></tr>
    <tr><td>Chiffre d'Affaires</td><td>$chiffre_affaires</td></tr>
    <tr><td>Charges Exploitation</td><td>$charges_exploitation</td></tr>
    <tr><td>EBE</td><td>$ebe</td></tr>
    <tr><td>Produits financiers</td><td>$produits_financiers</td></tr>
    <tr><td>Charges financières</td><td>$charges_financieres</td></tr>
    <tr><td>CAF</td><td>$caf</td></tr>
    <tr><td>Résultat exceptionnel</td><td>$resultat_exceptionnel</td></tr>
    <tr><td>Résultat net</td><td>$resultat_net</td></tr>
    </table>";

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('compte_resultat.pdf','D');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Compte de Résultat</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-4 bg-light">

<h2>Compte de Résultat au <?= htmlspecialchars($periode) ?></h2>

<table class="table table-striped mt-3">
<tr><th>Rubrique</th><th>Montant</th></tr>
<tr><td>Chiffre d'Affaires</td><td><?= number_format($chiffre_affaires,2,',',' ') ?></td></tr>
<tr><td>Charges Exploitation</td><td><?= number_format($charges_exploitation,2,',',' ') ?></td></tr>
<tr><td><b>EBE</b></td><td><b><?= number_format($ebe,2,',',' ') ?></b></td></tr>
<tr><td>Produits financiers</td><td><?= number_format($produits_financiers,2,',',' ') ?></td></tr>
<tr><td>Charges financières</td><td><?= number_format($charges_financieres,2,',',' ') ?></td></tr>
<tr><td><b>CAF</b></td><td><b><?= number_format($caf,2,',',' ') ?></b></td></tr>
<tr><td>Résultat exceptionnel</td><td><?= number_format($resultat_exceptionnel,2,',',' ') ?></td></tr>
<tr><td><b>Résultat net</b></td><td><b><?= number_format($resultat_net,2,',',' ') ?></b></td></tr>
</table>

<div class="mt-3">
    <a href="?export_excel=1" class="btn btn-success">Exporter Excel</a>
    <a href="?export_pdf=1" class="btn btn-danger">Exporter PDF</a>
</div>

</body>
</html>

