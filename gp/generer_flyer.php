<?php
// Forcer l'encodage UTF-8
header('Content-Type: text/html; charset=utf-8');
require_once 'fpdf186/fpdf.php';

class PDF_Offre extends FPDF
{
    function Header()
    {
        // Logo très petit et très haut
        if (file_exists('logo.jpg')) {
            $this->Image('logo.jpg', 8, 3, 20);
        }
        // Titre entreprise à droite (plus petit)
        $this->SetY(5);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(255, 140, 0);
        $this->Cell(0, 4, 'Dieynaba GP Holding', 0, 1, 'R');
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 3, 'Le pont entre l’Afrique et l’Europe', 0, 1, 'R');
        // Ligne fine
        $this->Ln(3);
        $this->SetDrawColor(255, 140, 0);
        $this->Line(10, 18, 200, 18);
        // CONTENU TRÈS BAS (Y = 55 pour être sûr)
        $this->SetY(55);
    }

    function Footer()
    {
        $this->SetY(-25);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 5, 'Dieynaba GP Holding - Hann Maristes, Dakar | Contact : +33 7 58 68 63 48', 0, 0, 'C');
        $this->Ln(4);
        $this->Cell(0, 5, 'www.dieynaba.com | contact@dieynaba.com', 0, 0, 'C');
    }
}

$pdf = new PDF_Offre();
$pdf->AddPage();

// TITRE PRINCIPAL (très bas)
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(0, 12, 'ENVOYEZ VOS COLIS EN TOUTE SIMPLICITE', 0, 1, 'C');
$pdf->Ln(8);

// TEXTE DE PRÉSENTATION
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 7, utf8_decode("Que vous soyez à Dakar ou à Paris, Dieynaba GP Holding vous propose un service de fret fiable, suivi en temps réel et accompagnement personnalisé."));
$pdf->Ln(10);

// SECTION NOS SERVICES (rouge)
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(0, 8, 'NOS SERVICES', 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 6, utf8_decode("• Expédition de colis (Dakar ↔ Paris) dès 45€ HT\n• Suivi GPS + QR code personnalisé\n• Notification WhatsApp à chaque étape\n• Dédouanement inclus / Assistance 7j/7"));
$pdf->Ln(6);

// SECTION HORAIRES DES VOLS (rouge)
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(0, 8, 'HORAIRES DES VOLS', 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 6, utf8_decode("Paris → Dakar : tous les MARDI (départ 08h00, arrivée 14h30)\nDakar → Paris : tous les JEUDI (départ 11h00, arrivée 16h00)"));
$pdf->Ln(6);

// SECTION CONTACT & OFFRE (rouge)
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(0, 8, 'CONTACT & OFFRE', 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 6, utf8_decode("WhatsApp / Appel : +33 7 58 68 63 48 (France)\nOffre spéciale : -10% sur votre premier envoi avec le code DAKAR10"));
$pdf->Ln(12);

// TEXTE FINAL
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 6, utf8_decode("Venez visiter notre showroom à Hann Maristes (à côté de l'École franco-japonaise)"), 0, 1, 'C');

$pdf->Output('I', 'Flyer_Dieynaba_GP_Holding.pdf');
?>
