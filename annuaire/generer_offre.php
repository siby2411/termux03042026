<?php
require('config/db.php');
require('includes/fpdf.php');

if (!isset($_POST['telephone'])) { 
    header('Location: index.php'); 
    exit; 
}

$tel = trim($_POST['telephone']);

// 1. Recherche du partenaire dans l'annuaire
$stmt = $pdo->prepare("SELECT * FROM annuaire_medical WHERE telephone LIKE ? LIMIT 1");
$stmt->execute(['%' . $tel . '%']);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) { 
    die("Désolé, ce numéro ne correspond à aucun partenaire dans notre annuaire."); 
}

// 2. LOG : Enregistrement dans l'historique pour le module de suivi
try {
    $log = $pdo->prepare("INSERT INTO historique_offres (nom_client, telephone_client, categorie_client) VALUES (?, ?, ?)");
    $log->execute([$client['nom'], $client['telephone'], $client['categorie']]);
} catch (Exception $e) {
    // Continuer même si le log échoue
}

// 3. Classe PDF Haute Qualité OMEGA
class OMEGA_PDF extends FPDF {
    function Header() {
        if(file_exists('assets/omega.jpg')) $this->Image('assets/omega.jpg', 10, 8, 33);
        
        // Entête Droite
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(0, 51, 102); // Bleu Nuit Omega
        $this->Cell(0, 10, 'OMEGA INFORMATIQUE CONSULTING', 0, 1, 'R');
        
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(100);
        $this->Cell(0, 5, utf8_decode('Solutions Digitales & Maintenance Systèmes'), 0, 1, 'R');
        
        $this->Ln(15);
        $this->SetDrawColor(0, 51, 102);
        $this->SetLineWidth(0.8);
        $this->Line(10, 38, 200, 38);
    }

    function Footer() {
        $this->SetY(-25);
        $this->SetDrawColor(200);
        $this->Line(10, 272, 200, 272);
        
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(0);
        $this->Cell(0, 8, utf8_decode('Mr Mohamed Siby - Consultant en Informatique'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(80);
        $this->Cell(0, 4, utf8_decode('Sacré Cœur 3 VDN, Dakar | Tel: 77 654 28 03 | Email: m.siby@omega-consulting.sn'), 0, 1, 'C');
    }
}

// 4. Initialisation du Document
$pdf = new OMEGA_PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

// Date et Lieu
$pdf->Ln(5);
$pdf->Cell(0, 10, utf8_decode('Dakar, le ' . date('d/m/Y')), 0, 1, 'R');

// Bloc Destinataire (Look Premium)
$pdf->Ln(10);
$pdf->SetFillColor(245, 247, 250);
$pdf->SetDrawColor(220, 230, 240);
$pdf->Rect(110, 55, 90, 30, 'DF'); // Cadre pour l'adresse client

$pdf->SetXY(115, 60);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(100);
$pdf->Cell(0, 5, utf8_decode('DESTINATAIRE :'), 0, 1);
$pdf->SetXY(115, 66);
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(0, 80, 160);
$pdf->Cell(0, 7, utf8_decode($client['nom']), 0, 1);
$pdf->SetXY(115, 74);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
$pdf->Cell(0, 5, utf8_decode($client['adresse']), 0, 1);
$pdf->SetXY(115, 79);
$pdf->Cell(0, 5, utf8_decode($client['zone_geographique']), 0, 1);

// 5. Contenu Dynamique selon la Catégorie
$pdf->SetXY(10, 95);
$objet = "";
$corps = "";

switch ($client['categorie']) {
    case 'PHARMACIE':
        $objet = "OBJET : PROPOSITION DE MAINTENANCE ET GESTION OFFICINALE (OMEGA PHARMA)";
        $corps = "Monsieur le Docteur en Pharmacie,\n\n" .
                 "La gestion d'une officine moderne exige une précision absolue sur les stocks et les péremptions (DLC). OMEGA Informatique vous propose d'optimiser votre structure avec :\n\n" .
                 "• Installation et configuration du système OMEGA Pharma Pro.\n" .
                 "• Maintenance préventive de vos terminaux de vente et serveurs.\n" .
                 "• Sécurisation de vos données de facturation et tiers-payant.\n" .
                 "• Support technique prioritaire 6j/7.";
        break;

    case 'CLINIQUE':
    case 'URGENCE':
        $objet = "OBJET : SOLUTIONS DE DIGITALISATION DU PARCOURS PATIENT (LRP)";
        $corps = "Monsieur le Directeur,\n\n" .
                 "Pour garantir la fluidité de la prise en charge de vos patients, OMEGA Informatique CONSULTING vous propose son expertise en systèmes hospitaliers :\n\n" .
                 "• Digitalisation complète des dossiers médicaux et archivage.\n" .
                 "• Interconnexion réseau de vos services (Accueil, Labo, Radiologie).\n" .
                 "• Mise en place de tableaux de bord financiers pour la direction.\n" .
                 "• Maintenance globale de votre infrastructure IT.";
        break;

    case 'DENTAIRE':
        $objet = "OBJET : MODERNISATION ET GESTION NUMÉRIQUE DU CABINET DENTAIRE";
        $corps = "Monsieur le Docteur,\n\n" .
                 "Nous vous proposons d'améliorer l'efficacité de votre cabinet via nos solutions dédiées :\n\n" .
                 "• Gestion numérique des rendez-vous et fiches patients.\n" .
                 "• Archivage sécurisé des clichés de radiologie numérique.\n" .
                 "• Maintenance technique de vos équipements informatiques de soins.";
        break;

    default:
        $objet = "OBJET : ACCOMPAGNEMENT INFORMATIQUE ET MAINTENANCE SYSTÈME";
        $corps = "Monsieur le Directeur,\n\n" .
                 "OMEGA Informatique CONSULTING vous propose ses services pour la gestion et l'évolution de votre parc informatique.";
        break;
}

// Affichage Objet (Bandeau de couleur)
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(0, 51, 102);
$pdf->SetTextColor(255);
$pdf->Cell(0, 12, utf8_decode("  " . $objet), 0, 1, 'L', true);

// Affichage Corps
$pdf->Ln(10);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 7, utf8_decode($corps));

// Bloc Pourquoi OMEGA ?
$pdf->Ln(15);
$pdf->SetFillColor(240, 245, 250);
$pdf->SetDrawColor(0, 51, 102);
$pdf->Rect(10, $pdf->GetY(), 190, 28, 'DF');

$pdf->SetXY(15, $pdf->GetY() + 4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, utf8_decode("VOTRE AVANTAGE OMEGA :"), 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, utf8_decode("- Expertise reconnue sur le marché Sénégalais."), 0, 1);
$pdf->Cell(0, 5, utf8_decode("- Intervention rapide sur site (Dakar et banlieue)."), 0, 1);
$pdf->Cell(0, 5, utf8_decode("- Solutions évolutives et conformes au SYSCOHADA."), 0, 1);

// Signature
$pdf->Ln(25);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, utf8_decode('Le Consultant Expert,'), 0, 1, 'R');
$pdf->Ln(5);
$pdf->Cell(0, 10, utf8_decode('Mr Mohamed Siby'), 0, 1, 'R');

// Sortie finale
$pdf->Output('I', 'Offre_Omega_' . str_replace(' ', '_', $client['nom']) . '.pdf');
