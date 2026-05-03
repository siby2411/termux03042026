<?php
require_once 'config.php';

if(isset($_GET['debut']) && isset($_GET['fin'])) {
    $debut = $_GET['debut'];
    $fin = $_GET['fin'];
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Récupérer les données pour le PDF
    $query = $conn->prepare("
        SELECT 
            SUM(total_ttc) as ca_total,
            SUM(total_ht) as ca_ht,
            SUM(tva) as total_tva,
            COUNT(*) as nb_commandes
        FROM commandes 
        WHERE DATE(date_commande) BETWEEN ? AND ?
    ");
    $query->execute([$debut, $fin]);
    $stats = $query->fetch();
    
    // Produits les plus vendus
    $query_produits = $conn->prepare("
        SELECT 
            s.nom,
            SUM(ca.quantite) as total_vendu,
            SUM(ca.sous_total) as chiffre_affaires
        FROM commande_articles ca
        LEFT JOIN services s ON ca.service_id = s.id
        LEFT JOIN commandes c ON ca.commande_id = c.id
        WHERE DATE(c.date_commande) BETWEEN ? AND ?
        GROUP BY s.id, s.nom
        ORDER BY total_vendu DESC
        LIMIT 10
    ");
    $query_produits->execute([$debut, $fin]);
    $produits_vendus = $query_produits->fetchAll();
    
    // Générer le PDF avec TCPDF
    require_once('tcpdf/tcpdf.php');
    
    class PDF extends TCPDF {
        // Header personnalisé
        public function Header() {
            $this->SetFont('helvetica', 'B', 16);
            $this->Cell(0, 10, 'Rapport Financier - Pressing Pro', 0, 1, 'C');
            $this->SetFont('helvetica', '', 10);
            $this->Cell(0, 10, 'Période: ' . $_GET['debut'] . ' au ' . $_GET['fin'], 0, 1, 'C');
            $this->Ln(5);
        }
        
        // Footer personnalisé
        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/'. $this->getAliasNbPages(), 0, 0, 'C');
        }
    }
    
    // Créer le PDF
    $pdf = new PDF();
    $pdf->SetCreator('Pressing Pro');
    $pdf->SetAuthor('Pressing Pro');
    $pdf->SetTitle('Rapport Financier');
    $pdf->AddPage();
    
    // Statistiques principales
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Statistiques Principales', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    $pdf->Cell(0, 8, 'Chiffre d\'affaires TTC: ' . number_format($stats['ca_total'], 2, ',', ' ') . ' €', 0, 1);
    $pdf->Cell(0, 8, 'Chiffre d\'affaires HT: ' . number_format($stats['ca_ht'], 2, ',', ' ') . ' €', 0, 1);
    $pdf->Cell(0, 8, 'TVA collectée: ' . number_format($stats['total_tva'], 2, ',', ' ') . ' €', 0, 1);
    $pdf->Cell(0, 8, 'Nombre de commandes: ' . $stats['nb_commandes'], 0, 1);
    
    $pdf->Ln(10);
    
    // Tableau des produits vendus
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Top 10 des Services Vendus', 0, 1);
    
    // En-tête du tableau
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(80, 8, 'Service', 1, 0, 'L', true);
    $pdf->Cell(40, 8, 'Quantité', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'CA (€)', 1, 1, 'R', true);
    
    // Données du tableau
    $pdf->SetFont('helvetica', '', 9);
    foreach($produits_vendus as $produit) {
        $pdf->Cell(80, 8, $produit['nom'], 1, 0, 'L');
        $pdf->Cell(40, 8, $produit['total_vendu'], 1, 0, 'C');
        $pdf->Cell(40, 8, number_format($produit['chiffre_affaires'], 2, ',', ' '), 1, 1, 'R');
    }
    
    $pdf->Ln(10);
    
    // Notes
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->MultiCell(0, 8, 'Rapport généré le ' . date('d/m/Y à H:i') . "\nPressing Pro - Système de gestion");
    
    // Output du PDF
    $pdf->Output('rapport_financier_' . $debut . '_' . $fin . '.pdf', 'D');
    exit;
    
} else {
    header('Location: etat_financier.php');
    exit;
}
?>
