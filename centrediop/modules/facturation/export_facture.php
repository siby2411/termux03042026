<?php
require_once '../../includes/db.php';
require_once '../../includes/libs/tcpdf/tcpdf.php';

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$query = $conn->prepare("SELECT * FROM factures WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$facture = $query->get_result()->fetch_assoc();

if (!$facture) { die("Facture non trouvée."); }

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

$html = "
    <h1 style='text-align:center;'>Centre Mamadou Diop</h1>
    <h2 style='text-align:center;'>Facture N° " . $facture['id'] . "</h2>
    <hr>
    <p><strong>Patient :</strong> " . htmlspecialchars($facture['nom_patient']) . "</p>
    <p><strong>Date :</strong> " . $facture['date_creation'] . "</p>
    <p><strong>Montant Total :</strong> " . number_format($facture['montant'], 2) . " FCFA</p>
";

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Facture_' . $facture['id'] . '.pdf', 'I');
?>
