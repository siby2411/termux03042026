<?php
require_once('../../includes/libs/fpdf186/fpdf.php');
require_once('../../includes/classes/Database.php');

$db = (new Database())->getConnection();
$id_lavage = $_GET['id'] ?? 0;

// On récupère les infos du lavage et du véhicule
$stmt = $db->prepare("SELECT l.*, v.immatriculation, v.marque 
                       FROM lavage l 
                       JOIN vehicules v ON l.id_vehicule = v.id_vehicule 
                       WHERE l.id_lavage = ?");
$stmt->execute([$id_lavage]);
$data = $stmt->fetch();

// Format 80mm (Largeur standard ticket thermique)
$pdf = new FPDF('P', 'mm', [80, 150]); 
$pdf->AddPage();
$pdf->SetMargins(5, 5, 5);

// Header Ticket
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 5, 'OMEGA TECH GARAGE', 0, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 4, 'Dakar, Senegal', 0, 1, 'C');
$pdf->Cell(0, 4, 'Tel: +221 77 XXX XX XX', 0, 1, 'C');
$pdf->Ln(2);
$pdf->Cell(0, 0, '', 'T', 1); // Ligne de séparation
$pdf->Ln(2);

// Infos Client/Véhicule
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 5, 'TICKET LAVAGE N: ' . $id_lavage, 0, 1, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, 'Date: ' . date('d/m/Y H:i'), 0, 1, 'L');
$pdf->Cell(0, 5, 'Vehicule: ' . $data['marque'] . ' [' . $data['immatriculation'] . ']', 0, 1, 'L');
$pdf->Ln(2);

// Détails Service
$pdf->Cell(0, 0, '', 'T', 1);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(45, 5, 'DESIGNATION', 0, 0, 'L');
$pdf->Cell(25, 5, 'TOTAL', 0, 1, 'R');
$pdf->SetFont('Arial', '', 8);

// Exemple de ligne (à adapter selon votre table lavage)
$pdf->Cell(45, 5, utf8_decode('Lavage Complet + Moteur'), 0, 0, 'L');
$pdf->Cell(25, 5, number_format($data['montant'], 0, '.', ' ') . ' F', 0, 1, 'R');

$pdf->Ln(3);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, 'NET A PAYER', 0, 0, 'L');
$pdf->Cell(30, 7, number_format($data['montant'], 0, '.', ' ') . ' FCFA', 1, 1, 'R');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 7);
$pdf->MultiCell(0, 4, 'Merci de votre confiance. Gardez ce ticket pour recuperer votre vehicule.', 0, 'C');

$pdf->Output('I', 'Ticket_Lavage_' . $id_lavage . '.pdf');
