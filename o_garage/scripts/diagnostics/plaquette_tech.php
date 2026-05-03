<?php
require_once('../../includes/libs/fpdf186/fpdf.php');
require_once('../../includes/classes/Database.php');

$db = (new Database())->getConnection();
$id_int = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT i.*, v.immatriculation, v.marque, v.modele, m.nom_complet as expert FROM interventions i JOIN vehicules v ON i.id_vehicule = v.id_vehicule JOIN mecaniciens m ON i.id_mecanicien_principal = m.id_mecanicien WHERE i.id_intervention = ?");
$stmt->execute([$id_int]);
$data = $stmt->fetch();

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Border
$pdf->SetLineWidth(1);
$pdf->Rect(5, 5, 287, 200);
$pdf->SetLineWidth(0.2);
$pdf->Rect(7, 7, 283, 196);

// Title
$pdf->SetFont('Arial', 'B', 24);
$pdf->SetTextColor(13, 71, 161);
$pdf->Cell(0, 20, 'PLAQUETTE DE DIAGNOSTIC PROFESSIONNEL', 0, 1, 'C');
$pdf->SetDrawColor(255, 109, 0);
$pdf->Line(80, 28, 215, 28);

// Content
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0);
$pdf->Cell(0, 10, utf8_decode('VÉHICULE : ' . $data['marque'] . ' ' . $data['modele'] . ' [' . $data['immatriculation'] . ']'), 0, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(240);
$pdf->Cell(0, 8, ' 1. ANALYSE DES SYMPTOMES', 0, 1, 'L', true);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 8, utf8_decode($data['description_panne']), 0, 'L');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, ' 2. RESOLUTION TECHNIQUE OMEGA TECH', 0, 1, 'L', true);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 8, utf8_decode($data['diagnostic_technique']), 0, 'L');

// Expert & Date
$pdf->SetY(170);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'CERTIFIE PAR L\'EXPERT : ' . strtoupper(utf8_decode($data['expert'])), 0, 1, 'R');
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 5, 'Fait a Dakar, le ' . date('d/m/Y'), 0, 1, 'R');

$pdf->Output('I', 'Diag_' . $data['immatriculation'] . '.pdf');
