<?php
require_once 'db_connect.php';

$fpdf_path = __DIR__ . '/fpdf186/fpdf.php';
if (!file_exists($fpdf_path)) {
    die("FPDF non trouve. Veuillez verifier l'installation.");
}
require_once $fpdf_path;

$stats = [];
$stats['total_colis'] = $pdo->query("SELECT COUNT(*) FROM colis")->fetchColumn();
$stats['en_transit'] = $pdo->query("SELECT COUNT(*) FROM colis WHERE statut NOT IN ('livre','arrivee')")->fetchColumn();
$stats['livres'] = $pdo->query("SELECT COUNT(*) FROM colis WHERE statut='livre'")->fetchColumn();
$stats['produits'] = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$stats['vetements'] = $pdo->query("SELECT COUNT(*) FROM vetements")->fetchColumn();
$stats['bijoux'] = $pdo->query("SELECT COUNT(*) FROM bijouterie")->fetchColumn();
$stats['negoce'] = $pdo->query("SELECT COUNT(*) FROM negoce")->fetchColumn();
$stats['clients'] = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();

class PDF_Offre extends FPDF
{
    var $logo_printed = false;
    
    function Header()
    {
        if (!$this->logo_printed && file_exists(__DIR__ . '/logo.jpg')) {
            $this->Image(__DIR__ . '/logo.jpg', 10, 5, 22);
            $this->logo_printed = true;
        }
        $this->SetY(8);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(255, 140, 0);
        $this->Cell(0, 5, 'Dieynaba GP Holding', 0, 1, 'R');
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 4, 'Transport international - E-commerce - Logistique', 0, 1, 'R');
        $this->Ln(6);
        $this->SetDrawColor(255, 140, 0);
        $this->Line(10, 24, 200, 24);
        $this->SetY(30);
    }
    
    function Footer()
    {
        $this->SetY(-22);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 4, 'Dieynaba GP Holding - Hann Maristes, Dakar | Saint-Denis, France', 0, 0, 'C');
        $this->Ln(3);
        $this->Cell(0, 4, 'Tel: +221 77 654 28 03 | +33 7 58 68 63 48 | Email: contact@dieynaba.com', 0, 0, 'C');
        $this->Ln(3);
        $this->Cell(0, 4, 'Page ' . $this->PageNo() . ' / 2', 0, 0, 'C');
    }
    
    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(255, 140, 0);
        $this->Cell(0, 6, $title, 0, 1, 'L');
        $this->SetDrawColor(255, 140, 0);
        $this->Line($this->GetX(), $this->GetY(), 190, $this->GetY());
        $this->Ln(3);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 9);
    }
}

$pdf = new PDF_Offre();
$pdf->AddPage();
$pdf->SetY(38);

// Titre
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(0, 8, 'OFFRE TECHNIQUE ET COMMERCIALE', 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, 'Institutionnelle & Professionnelle', 0, 1, 'C');
$pdf->Ln(4);

// Presentation
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 4.5, "Dieynaba GP Holding est une structure internationale specialisee dans le transport de marchandises entre la France et le Senegal, le e-commerce et la vente de produits haut de gamme.");
$pdf->Ln(2);

// Chiffres cles
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(255, 140, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(47, 8, 'Chiffres cles', 1, 0, 'C', true);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(47, 8, $stats['total_colis'] . ' colis traites', 1, 0, 'C');
$pdf->Cell(47, 8, $stats['clients'] . ' clients actifs', 1, 0, 'C');
$pdf->Cell(47, 8, ($stats['en_transit'] + $stats['livres']) . ' colis en cours', 1, 1, 'C');
$pdf->Ln(4);

// Fret - version plus compacte
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 140, 0);
$pdf->Cell(0, 6, '1. MODULE FRET & LOGISTIQUE', 0, 1, 'L');
$pdf->SetDrawColor(255, 140, 0);
$pdf->Line($pdf->GetX(), $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 4, "Transport bidirectionnel Paris ↔ Dakar. Vols : mardis (Paris→Dakar) et jeudis (Dakar→Paris).");
$pdf->Ln(1);

// Boutiques compactes (2x2)
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 140, 0);
$pdf->Cell(0, 6, '2. BOUTIQUES EN LIGNE', 0, 1, 'L');
$pdf->Line($pdf->GetX(), $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(2);

$boutiques = [
    ['Epicerie', $stats['produits'] . ' prod', 'Huile, miel, crevettes'],
    ['Mode', $stats['vetements'] . ' art', 'Boubous, kaftans, wax'],
    ['Joaillerie', $stats['bijoux'] . ' ref', 'Bagues, colliers, montres'],
    ['Negoce', $stats['negoce'] . ' prod', 'High-Tech, mobilier']
];

foreach ($boutiques as $i => $b) {
    $x = 15 + ($i % 2) * 95;
    $y = $pdf->GetY();
    $pdf->SetXY($x, $y);
    $pdf->SetFillColor(245, 245, 245);
    $pdf->Cell(90, 7, $b[0] . ' - ' . $b[1], 1, 1, 'C', true);
    $pdf->SetX($x);
    $pdf->Cell(90, 11, $b[2], 1, 1, 'C');
    if ($i % 2 == 1) $pdf->SetY($pdf->GetY() + 2);
}
$pdf->SetY($pdf->GetY() + 10);

// Dashboard (remonte sur page1)
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 140, 0);
$pdf->Cell(0, 6, '3. TABLEAU DE BORD & GESTION FINANCIERE', 0, 1, 'L');
$pdf->Line($pdf->GetX(), $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(0, 4, "- Etats financiers separes par secteur (Fret, Epicerie, Mode, Joaillerie, Negoce)\n- Etats consolides de la holding\n- Gestion des charges et depenses\n- Courbes d'evolution et benefices nets");
$pdf->Ln(4);

// ==================== PAGE 2 ====================
$pdf->AddPage();
$pdf->SetY(35);

// Communication
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 140, 0);
$pdf->Cell(0, 6, '4. COMMUNICATION & CLIENT', 0, 1, 'L');
$pdf->Line($pdf->GetX(), $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(0, 4.5, "- API WhatsApp Business pour notifications automatiques\n- Generation et envoi de QR codes par colis\n- Offres commerciales personnalisees en PDF\n- Prospectus et campagnes marketing integres");
$pdf->Ln(5);

// Tarifs
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 140, 0);
$pdf->Cell(0, 6, '5. TARIFS & CONDITIONS', 0, 1, 'L');
$pdf->Line($pdf->GetX(), $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(90, 7, 'Service fret (HT)', 1, 0, 'C');
$pdf->Cell(90, 7, 'Boutiques', 1, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(90, 6, 'Colis < 5 kg : 45 EUR', 1, 0);
$pdf->Cell(90, 6, 'Commission vente : 12%', 1, 1);
$pdf->Cell(90, 6, 'Colis 5-10 kg : 65 EUR', 1, 0);
$pdf->Cell(90, 6, 'Marketing : inclus', 1, 1);
$pdf->Cell(90, 6, 'Abonnement 10 colis : 350 EUR', 1, 0);
$pdf->Cell(90, 6, 'Support technique : inclus', 1, 1);
$pdf->Ln(6);

// Contacts
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 140, 0);
$pdf->Cell(0, 7, 'CONTACTS INSTITUTIONNELS', 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 5, "Dieynaba Keita - Directrice Generale", 0, 1, 'C');
$pdf->Cell(0, 5, "WhatsApp / Tel: +221 77 654 28 03 | +33 7 58 68 63 48", 0, 1, 'C');
$pdf->Cell(0, 5, "Email: contact@dieynaba.com", 0, 1, 'C');
$pdf->Cell(0, 5, "Showroom: Hann Maristes - A cote Ecole Franco-Japonaise, Dakar", 0, 1, 'C');
$pdf->Cell(0, 5, "Antenne France: Saint-Denis, Ile-de-France", 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, '"Dieynaba GP Holding - Le pont entre l\'Afrique et l\'Europe"', 0, 1, 'C');

$pdf->Output('I', 'Offre_Dieynaba_GP_Holding.pdf');
?>
