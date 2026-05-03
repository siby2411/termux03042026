<?php
// export_flux_tresorerie_pdf.php
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

// Utiliser une bibliothèque PDF comme TCPDF ou mPDF
// Ici, nous allons utiliser TCPDF (à installer via composer)

// Si TCPDF n'est pas installé, on redirige vers la version HTML
if (!class_exists('TCPDF')) {
    // Rediriger vers la version HTML
    $id_exercice = isset($_GET['id_exercice']) ? intval($_GET['id_exercice']) : $_SESSION['id_exercice'];
    header("Location: tableau_flux_tresorerie.php?id_exercice=$id_exercice");
    exit();
}

// Inclure TCPDF
require_once('tcpdf/tcpdf.php');

$id_exercice = isset($_GET['id_exercice']) ? intval($_GET['id_exercice']) : $_SESSION['id_exercice'];

// Récupérer les données de l'exercice
$sql_exercice = "SELECT * FROM exercices_comptables WHERE id_exercice = :id_exercice";
$stmt = $pdo->prepare($sql_exercice);
$stmt->execute([':id_exercice' => $id_exercice]);
$exercice = $stmt->fetch();

if (!$exercice) {
    die("Exercice non trouvé");
}

$date_debut = $exercice['date_debut'];
$date_fin = $exercice['date_fin'];

// Calculer les flux de trésorerie
$flux = calculerFluxTresorerie($pdo, $id_exercice, $date_debut, $date_fin);

// Créer un nouveau document PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Informations du document
$pdf->SetCreator('SYSCO OHADA');
$pdf->SetAuthor('SYSCO Solutions');
$pdf->SetTitle('Tableau des Flux de Trésorerie');
$pdf->SetSubject('Flux de Trésorerie OHADA');
$pdf->SetKeywords('OHADA, Flux, Trésorerie, Comptabilité');

// Marges
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Supprimer l'en-tête et le pied de page par défaut
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Ajouter une page
$pdf->AddPage();

// Contenu HTML
$html = '
<style>
    .header { text-align: center; margin-bottom: 20px; }
    .header h1 { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
    .header h2 { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
    .header .dates { font-size: 12px; margin-bottom: 5px; }
    .header .norme { font-size: 10px; font-style: italic; }
    
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding: 5px; border: 1px solid #000; }
    th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
    .section-header { background-color: #e0e0e0; font-weight: bold; }
    .subtotal { font-weight: bold; background-color: #f8f8f8; }
    .total { font-weight: bold; background-color: #e8e8e8; border-top: 2px solid #000; border-bottom: 2px solid #000; }
    .indent { padding-left: 20px !important; }
    .indent-2 { padding-left: 40px !important; }
    .montant { text-align: right; font-family: courier; }
    .negative { color: #d00; }
    .positive { color: #090; }
    
    .signatures { margin-top: 40px; border-top: 1px solid #000; padding-top: 20px; }
    .signature-box { width: 45%; text-align: center; padding-top: 40px; border-top: 1px solid #000; display: inline-block; }
    .notes { margin-top: 30px; font-size: 9px; color: #666; }
</style>

<div class="header">
    <h1>TABLEAU DES FLUX DE TRÉSORERIE</h1>
    <h2>Exercice ' . date('Y', strtotime($exercice['date_debut'])) . '</h2>
    <div class="dates">Du ' . date('d/m/Y', strtotime($date_debut)) . ' au ' . date('d/m/Y', strtotime($date_fin)) . '</div>
    <div class="norme">Conforme aux normes OHADA SYSCOHADA - Méthode indirecte</div>
</div>

<table>
    <thead>
        <tr>
            <th width="70%">DÉSIGNATION</th>
            <th width="30%">MONTANT (FCFA)</th>
        </tr>
    </thead>
    <tbody>
        <!-- I. FLUX DE TRÉSORERIE D\'EXPLOITATION -->
        <tr class="section-header">
            <td colspan="2"><strong>I. FLUX DE TRÉSORERIE D\'EXPLOITATION</strong></td>
        </tr>
        
        <tr>
            <td class="indent">Résultat net de l\'exercice</td>
            <td class="montant">' . format_montant($flux['exploitation']['produits_exploitation'] - $flux['exploitation']['charges_exploitation']) . '</td>
        </tr>
        
        <!-- Ajouter les autres lignes comme dans le fichier HTML -->
        <!-- ... -->
        
    </tbody>
</table>

<div class="signatures">
    <div class="signature-box">Le Directeur Financier</div>
    <div class="signature-box">Le Commissaire aux Comptes</div>
</div>

<div class="notes">
    <p><strong>Notes :</strong></p>
    <p>1. Le tableau des flux de trésorerie est établi selon la méthode indirecte conformément aux normes OHADA.</p>
    <p>2. Date d\'établissement : ' . date('d/m/Y') . '</p>
</div>
';

// Écrire le HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Générer le PDF
$pdf->Output('flux_tresorerie_' . date('Y') . '.pdf', 'I');
