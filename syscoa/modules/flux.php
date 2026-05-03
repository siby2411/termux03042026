<?php
// tableau_flux_tresorerie.php
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

// Récupérer l'exercice courant
$id_exercice = $_SESSION['id_exercice'];

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

// Calculs des totaux
// 1. Flux d'exploitation
$flux_exploitation = $flux['exploitation']['produits_exploitation'] 
                   - $flux['exploitation']['charges_exploitation']
                   - $flux['exploitation']['variation_stocks']
                   + $flux['exploitation']['variation_clients']
                   + $flux['exploitation']['variation_fournisseurs']
                   - $flux['exploitation']['variation_personnel']
                   - $flux['exploitation']['variation_etat']
                   - $flux['exploitation']['impot_societes'];

// 2. Flux d'investissement
$flux_investissement = -$flux['investissement']['acquisitions_immobilisations']
                     + $flux['investissement']['cessions_immobilisations']
                     + $flux['investissement']['produits_cessions']
                     - $flux['investissement']['charges_cessions'];

// 3. Flux de financement
$flux_financement = $flux['financement']['emprunts']
                  - $flux['financement']['remboursements_emprunts']
                  + $flux['financement']['augmentations_capital']
                  - $flux['financement']['dividendes']
                  - $flux['financement']['charges_financieres'];

// 4. Variation de trésorerie
$variation_tresorerie = $flux_exploitation + $flux_investissement + $flux_financement;

// Trésorerie initiale et finale
$tresorerie_initiale = $flux['variations']['tresorerie_initiale'] ?? 0;
$tresorerie_finale = $flux['variations']['tresorerie_finale'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau des Flux de Trésorerie - SYSCO OHADA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .header .dates {
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .header .norme {
            font-size: 12px;
            font-style: italic;
            color: #666;
        }
        
        .table-container {
            width: 100%;
            margin-bottom: 40px;
        }
        
        .table-flux {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .table-flux th,
        .table-flux td {
            padding: 8px 12px;
            border: 1px solid #000;
            text-align: left;
            vertical-align: top;
        }
        
        .table-flux th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .table-flux .section-header {
            background: #e0e0e0;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .table-flux .subtotal {
            font-weight: bold;
            background: #f8f8f8;
        }
        
        .table-flux .total {
            font-weight: bold;
            font-size: 14px;
            background: #e8e8e8;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        
        .table-flux .indent {
            padding-left: 30px !important;
        }
        
        .table-flux .indent-2 {
            padding-left: 50px !important;
        }
        
        .table-flux .montant {
            text-align: right;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }
        
        .table-flux .negative {
            color: #d00;
        }
        
        .table-flux .positive {
            color: #090;
        }
        
        .signatures {
            margin-top: 50px;
            width: 100%;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .signature-box {
            width: 300px;
            text-align: center;
            padding-top: 60px;
            border-top: 1px solid #000;
        }
        
        .signature-label {
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .notes {
            margin-top: 30px;
            font-size: 11px;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
        
        .notes h4 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
            
            .table-flux th,
            .table-flux td {
                padding: 6px 8px;
            }
            
            .header {
                margin-bottom: 20px;
            }
        }
        
        .print-controls {
            margin-bottom: 20px;
            text-align: center;
            background: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .btn {
            padding: 10px 20px;
            margin: 0 5px;
            border: 1px solid #007bff;
            background: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #0056b3;
            border-color: #0056b3;
        }
        
        .btn-print {
            background: #28a745;
            border-color: #28a745;
        }
        
        .btn-print:hover {
            background: #1e7e34;
            border-color: #1e7e34;
        }
        
        .btn-export {
            background: #17a2b8;
            border-color: #17a2b8;
        }
        
        .btn-export:hover {
            background: #117a8b;
            border-color: #117a8b;
        }
    </style>
</head>
<body>
    <!-- Contrôles d'impression -->
    <div class="print-controls no-print">
        <button class="btn btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer le tableau
        </button>
        <button class="btn btn-export" onclick="exportToPDF()">
            <i class="fas fa-file-pdf"></i> Exporter PDF
        </button>
        <button class="btn" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i> Retour
        </button>
    </div>
    
    <!-- En-tête -->
    <div class="header">
        <h1>TABLEAU DES FLUX DE TRÉSORERIE</h1>
        <h2>Exercice <?php echo date('Y', strtotime($exercice['date_debut'])); ?></h2>
        <div class="dates">
            Du <?php echo date('d/m/Y', strtotime($date_debut)); ?> au <?php echo date('d/m/Y', strtotime($date_fin)); ?>
        </div>
        <div class="norme">
            Conforme aux normes OHADA SYSCOHADA - Méthode indirecte
        </div>
    </div>
    
    <!-- Tableau des Flux de Trésorerie -->
    <div class="table-container">
        <table class="table-flux">
            <thead>
                <tr>
                    <th width="70%">DÉSIGNATION</th>
                    <th width="30%">MONTANT (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                <!-- I. FLUX DE TRÉSORERIE D'EXPLOITATION -->
                <tr class="section-header">
                    <td colspan="2">
                        <strong>I. FLUX DE TRÉSORERIE D'EXPLOITATION</strong>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Résultat net de l'exercice</td>
                    <td class="montant">
                        <?php echo format_montant($flux['exploitation']['produits_exploitation'] - $flux['exploitation']['charges_exploitation']); ?>
                    </td>
                </tr>
                
                <!-- Ajustements pour passage de la comptabilité d'engagement à la trésorerie -->
                <tr>
                    <td class="indent-2">+ Dotations aux amortissements et provisions</td>
                    <td class="montant">
                        <?php 
                        // Récupérer les dotations (comptes 68)
                        $sql_dotations = "SELECT SUM(debit - credit) as dotations 
                                         FROM ecritures e 
                                         JOIN comptes_ohada c ON e.compte_num = c.numero
                                         WHERE e.id_exercice = :id_exercice 
                                         AND c.numero LIKE '68%'
                                         AND e.date_ecriture BETWEEN :date_debut AND :date_fin";
                        $stmt = $pdo->prepare($sql_dotations);
                        $stmt->execute([
                            ':id_exercice' => $id_exercice,
                            ':date_debut' => $date_debut,
                            ':date_fin' => $date_fin
                        ]);
                        $dotations = $stmt->fetchColumn();
                        echo format_montant($dotations);
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent-2">+/- Variation des stocks</td>
                    <td class="montant <?php echo $flux['exploitation']['variation_stocks'] < 0 ? 'negative' : 'positive'; ?>">
                        <?php echo format_montant($flux['exploitation']['variation_stocks']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent-2">+/- Variation des créances clients</td>
                    <td class="montant <?php echo $flux['exploitation']['variation_clients'] < 0 ? 'negative' : 'positive'; ?>">
                        <?php echo format_montant($flux['exploitation']['variation_clients']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent-2">+/- Variation des dettes fournisseurs</td>
                    <td class="montant <?php echo $flux['exploitation']['variation_fournisseurs'] < 0 ? 'negative' : 'positive'; ?>">
                        <?php echo format_montant($flux['exploitation']['variation_fournisseurs']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent-2">+/- Variation des dettes de personnel</td>
                    <td class="montant <?php echo $flux['exploitation']['variation_personnel'] < 0 ? 'negative' : 'positive'; ?>">
                        <?php echo format_montant($flux['exploitation']['variation_personnel']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent-2">+/- Variation des dettes fiscales</td>
                    <td class="montant <?php echo $flux['exploitation']['variation_etat'] < 0 ? 'negative' : 'positive'; ?>">
                        <?php echo format_montant($flux['exploitation']['variation_etat']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent-2">- Impôt sur les sociétés payé</td>
                    <td class="montant negative">
                        <?php echo format_montant($flux['exploitation']['impot_societes']); ?>
                    </td>
                </tr>
                
                <tr class="subtotal">
                    <td class="indent">
                        <strong>Trésorerie générée par les activités d'exploitation (A)</strong>
                    </td>
                    <td class="montant">
                        <strong><?php echo format_montant($flux_exploitation); ?></strong>
                    </td>
                </tr>
                
                <!-- II. FLUX DE TRÉSORERIE D'INVESTISSEMENT -->
                <tr class="section-header">
                    <td colspan="2">
                        <strong>II. FLUX DE TRÉSORERIE D'INVESTISSEMENT</strong>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Acquisitions d'immobilisations</td>
                    <td class="montant negative">
                        <?php echo format_montant($flux['investissement']['acquisitions_immobilisations']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Cessions d'immobilisations</td>
                    <td class="montant positive">
                        <?php echo format_montant($flux['investissement']['cessions_immobilisations']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Produits de cessions</td>
                    <td class="montant positive">
                        <?php echo format_montant($flux['investissement']['produits_cessions']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Charges sur cessions</td>
                    <td class="montant negative">
                        <?php echo format_montant($flux['investissement']['charges_cessions']); ?>
                    </td>
                </tr>
                
                <tr class="subtotal">
                    <td class="indent">
                        <strong>Trésorerie générée par les activités d'investissement (B)</strong>
                    </td>
                    <td class="montant">
                        <strong><?php echo format_montant($flux_investissement); ?></strong>
                    </td>
                </tr>
                
                <!-- III. FLUX DE TRÉSORERIE DE FINANCEMENT -->
                <tr class="section-header">
                    <td colspan="2">
                        <strong>III. FLUX DE TRÉSORERIE DE FINANCEMENT</strong>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Augmentations de capital</td>
                    <td class="montant positive">
                        <?php echo format_montant($flux['financement']['augmentations_capital']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Nouveaux emprunts</td>
                    <td class="montant positive">
                        <?php echo format_montant($flux['financement']['emprunts']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Remboursements d'emprunts</td>
                    <td class="montant negative">
                        <?php echo format_montant($flux['financement']['remboursements_emprunts']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Dividendes versés</td>
                    <td class="montant negative">
                        <?php echo format_montant($flux['financement']['dividendes']); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Charges financières payées</td>
                    <td class="montant negative">
                        <?php echo format_montant($flux['financement']['charges_financieres']); ?>
                    </td>
                </tr>
                
                <tr class="subtotal">
                    <td class="indent">
                        <strong>Trésorerie générée par les activités de financement (C)</strong>
                    </td>
                    <td class="montant">
                        <strong><?php echo format_montant($flux_financement); ?></strong>
                    </td>
                </tr>
                
                <!-- IV. VARIATION DE TRÉSORERIE -->
                <tr class="section-header">
                    <td colspan="2">
                        <strong>IV. VARIATION DE TRÉSORERIE</strong>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Variation de trésorerie de l'exercice (A + B + C)</td>
                    <td class="montant">
                        <?php echo format_montant($variation_tresorerie); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="indent">Trésorerie à l'ouverture de l'exercice</td>
                    <td class="montant">
                        <?php echo format_montant($tresorerie_initiale); ?>
                    </td>
                </tr>
                
                <tr class="total">
                    <td>
                        <strong>TRÉSORERIE À LA CLÔTURE DE L'EXERCICE</strong>
                    </td>
                    <td class="montant">
                        <strong><?php echo format_montant($tresorerie_finale); ?></strong>
                    </td>
                </tr>
                
                <!-- Vérification -->
                <tr>
                    <td class="indent">Vérification : Trésorerie d'ouverture + Variation</td>
                    <td class="montant">
                        <?php echo format_montant($tresorerie_initiale + $variation_tresorerie); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-label">Le Directeur Financier</div>
            </div>
            <div class="signature-box">
                <div class="signature-label">Le Commissaire aux Comptes</div>
            </div>
        </div>
    </div>
    
    <!-- Notes -->
    <div class="notes">
        <h4>Notes :</h4>
        <p>1. Le tableau des flux de trésorerie est établi selon la méthode indirecte conformément aux normes OHADA.</p>
        <p>2. Les flux de trésorerie sont présentés nets de TVA.</p>
        <p>3. Les soldes de trésorerie comprennent les disponibilités (caisse et banque).</p>
        <p>4. Date d'établissement : <?php echo date('d/m/Y'); ?></p>
    </div>
    
    <!-- Pied de page -->
    <div class="footer">
        Document généré par SYSCO OHADA - Système Comptable OHADA - Version 2.0
        <br>
        <?php echo date('d/m/Y H:i'); ?> - Utilisateur : <?php echo $_SESSION['username']; ?>
    </div>
    
    <script>
    function exportToPDF() {
        // Simuler un clic sur le bouton d'impression avec redirection vers un script PDF
        window.open('export_flux_tresorerie_pdf.php?id_exercice=<?php echo $id_exercice; ?>', '_blank');
    }
    
    // Ajouter les styles pour l'impression
    document.addEventListener('DOMContentLoaded', function() {
        // Ajouter Font Awesome pour les icônes
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const faLink = document.createElement('link');
            faLink.rel = 'stylesheet';
            faLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
            document.head.appendChild(faLink);
        }
    });
    </script>
</body>
</html><?php
// tableau_flux_tresorerie.php
// TABLEAU DE FLUX DE TRÉSORERIE - Conforme OHADA SYSCOHADA

// Gestion des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclure la configuration de la base de données
require_once 'config/database.php';

// Définir formatMontant en utilisant format_montant si elle n'existe pas déjà
if (!function_exists('formatMontant')) {
    function formatMontant($montant) {
        // Utilise la fonction format_montant de config/database.php
        return format_montant($montant, 'FCFA');
    }
}

// Vérifier que la connexion à la base de données est établie
if (!isset($pdo)) {
    die("<div class='alert alert-danger'>Erreur de connexion à la base de données.</div>");
}

// Récupérer l'exercice courant
$sql_exercice = "SELECT id_exercice, annee, date_debut, date_fin 
                 FROM exercices_comptables 
                 WHERE statut = 'ouvert' 
                 ORDER BY annee DESC 
                 LIMIT 1";
try {
    $exercice_courant = $pdo->query($sql_exercice)->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div class='alert alert-danger'>Erreur lors de la récupération de l'exercice: " . $e->getMessage() . "</div>");
}

// REQUÊTES POUR LE TABLEAU DES FLUX DE TRÉSORERIE

// 1. Flux de trésorerie d'exploitation
$sql_flux_exploitation = "
    SELECT 
        compte_num,
        libelle,
        SUM(debit) as total_debit,
        SUM(credit) as total_credit,
        CASE 
            WHEN LEFT(compte_num, 1) = '7' THEN 'produit'
            WHEN LEFT(compte_num, 1) = '6' THEN 'charge'
            ELSE 'autre'
        END as type_flux
    FROM ecritures
    WHERE (LEFT(compte_num, 1) = '6' OR LEFT(compte_num, 1) = '7')
    " . ($exercice_courant ? "AND id_exercice = " . intval($exercice_courant['id_exercice']) : "") . "
    GROUP BY compte_num, libelle
    HAVING (SUM(debit) > 0 OR SUM(credit) > 0)
    ORDER BY compte_num
";

// 2. Flux de trésorerie d'investissement (comptes 2)
$sql_flux_investissement = "
    SELECT 
        compte_num,
        libelle,
        SUM(debit) as total_debit,
        SUM(credit) as total_credit
    FROM ecritures
    WHERE LEFT(compte_num, 1) = '2'
    " . ($exercice_courant ? "AND id_exercice = " . intval($exercice_courant['id_exercice']) : "") . "
    GROUP BY compte_num, libelle
    HAVING (SUM(debit) > 0 OR SUM(credit) > 0)
    ORDER BY compte_num
";

// 3. Flux de trésorerie de financement (comptes 1 et certaines parties de 16)
$sql_flux_financement = "
    SELECT 
        compte_num,
        libelle,
        SUM(debit) as total_debit,
        SUM(credit) as total_credit
    FROM ecritures
    WHERE LEFT(compte_num, 1) = '1'
    OR compte_num LIKE '16%'
    " . ($exercice_courant ? "AND id_exercice = " . intval($exercice_courant['id_exercice']) : "") . "
    GROUP BY compte_num, libelle
    HAVING (SUM(debit) > 0 OR SUM(credit) > 0)
    ORDER BY compte_num
";

// Exécution des requêtes
try {
    $flux_exploitation = $pdo->query($sql_flux_exploitation)->fetchAll(PDO::FETCH_ASSOC);
    $flux_investissement = $pdo->query($sql_flux_investissement)->fetchAll(PDO::FETCH_ASSOC);
    $flux_financement = $pdo->query($sql_flux_financement)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div class='alert alert-danger'>Erreur lors du calcul des flux de trésorerie: " . $e->getMessage() . "</div>");
}

// Calcul des totaux
$total_flux_exploitation = 0;
$total_flux_investissement = 0;
$total_flux_financement = 0;

foreach ($flux_exploitation as $flux) {
    if ($flux['type_flux'] == 'produit') {
        $total_flux_exploitation += $flux['total_credit'];
    } else {
        $total_flux_exploitation -= $flux['total_debit'];
    }
}

foreach ($flux_investissement as $flux) {
    $total_flux_investissement += ($flux['total_credit'] - $flux['total_debit']);
}

foreach ($flux_financement as $flux) {
    $total_flux_financement += ($flux['total_credit'] - $flux['total_debit']);
}

// Flux net de trésorerie
$flux_net_tresorerie = $total_flux_exploitation + $total_flux_investissement + $total_flux_financement;

// Calcul du solde de trésorerie initial (fin de l'année précédente)
$solde_initial = 0;
if ($exercice_courant) {
    $sql_solde_initial = "
        SELECT 
            SUM(CASE WHEN LEFT(compte_num, 1) = '5' THEN (credit - debit) ELSE 0 END) as solde_tresorerie
        FROM ecritures
        WHERE id_exercice < ? 
        AND date_ecriture < ?
        ORDER BY id_exercice DESC
        LIMIT 1
    ";
    try {
        $stmt = $pdo->prepare($sql_solde_initial);
        $stmt->execute([$exercice_courant['id_exercice'], $exercice_courant['date_debut']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $solde_initial = $result['solde_tresorerie'] ?? 0;
    } catch (PDOException $e) {
        // Ignorer l'erreur si pas de données précédentes
    }
}

// Solde de trésorerie final
$solde_final = $solde_initial + $flux_net_tresorerie;

// Vérifier si les données sont valides
$donnees_valides = (count($flux_exploitation) > 0 || count($flux_investissement) > 0 || count($flux_financement) > 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau des Flux de Trésorerie - Système OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .tft-header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; }
        .exploitation-section { border-left: 4px solid #198754; }
        .investissement-section { border-left: 4px solid #0d6efd; }
        .financement-section { border-left: 4px solid #6f42c1; }
        .total-section { border-left: 4px solid #fd7e14; }
        .montant { font-family: 'Courier New', monospace; font-weight: bold; }
        .badge-compte { font-size: 0.75rem; }
        .flux-positif { color: #198754; }
        .flux-negatif { color: #dc3545; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0, 0, 0, 0.02); }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card tft-header shadow">
                    <div class="card-body text-center py-4">
                        <h1 class="h2 mb-3"><i class="fas fa-money-bill-wave me-2"></i>Tableau des Flux de Trésorerie</h1>
                        <p class="lead mb-0">
                            <?php if ($exercice_courant): ?>
                                Exercice <?php echo htmlspecialchars($exercice_courant['annee']); ?> - 
                                Du <?php echo date('d/m/Y', strtotime($exercice_courant['date_debut'])); ?> 
                                au <?php echo date('d/m/Y', strtotime($exercice_courant['date_fin'])); ?>
                            <?php else: ?>
                                Tous exercices confondus
                            <?php endif; ?>
                        </p>
                        <small class="opacity-75">Conforme aux normes OHADA SYSCOHADA</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message d'alerte si pas de données -->
        <?php if (!$donnees_valides): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Aucune donnée disponible pour générer le tableau des flux de trésorerie.
                    <br>
                    <small>Vérifiez que des écritures ont été saisies.</small>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cartes de résumé -->
        <?php if ($donnees_valides): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm h-100">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-industry fa-2x mb-2"></i>
                        <h4><?php echo formatMontant($total_flux_exploitation); ?></h4>
                        <p class="mb-1">Flux d'exploitation</p>
                        <small><?php echo count($flux_exploitation); ?> ligne(s)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm h-100">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-building fa-2x mb-2"></i>
                        <h4><?php echo formatMontant($total_flux_investissement); ?></h4>
                        <p class="mb-1">Flux d'investissement</p>
                        <small><?php echo count($flux_investissement); ?> ligne(s)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-purple text-white shadow-sm h-100" style="background-color: #6f42c1;">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                        <h4><?php echo formatMontant($total_flux_financement); ?></h4>
                        <p class="mb-1">Flux de financement</p>
                        <small><?php echo count($flux_financement); ?> ligne(s)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card <?php echo $flux_net_tresorerie >= 0 ? 'bg-warning' : 'bg-danger'; ?> text-white shadow-sm h-100">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-calculator fa-2x mb-2"></i>
                        <h4><?php echo formatMontant($flux_net_tresorerie); ?></h4>
                        <p class="mb-1">Flux net de trésorerie</p>
                        <small>Solde de l'exercice</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détail du tableau des flux de trésorerie -->
        <div class="row">
            <!-- Activités d'exploitation -->
            <div class="col-12 mb-4">
                <div class="card shadow exploitation-section">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-industry me-2"></i>ACTIVITÉS D'EXPLOITATION</h5>
                        <span class="badge bg-light text-dark"><?php echo count($flux_exploitation); ?> opérations</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">Compte</th>
                                        <th>Libellé</th>
                                        <th width="120" class="text-end">Débit</th>
                                        <th width="120" class="text-end">Crédit</th>
                                        <th width="150" class="text-end">Flux net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($flux_exploitation) > 0): ?>
                                        <?php foreach ($flux_exploitation as $flux): 
                                            $flux_net = ($flux['type_flux'] == 'produit') ? $flux['total_credit'] : -$flux['total_debit'];
                                        ?>
                                        <tr>
                                            <td><span class="badge bg-primary badge-compte"><?php echo $flux['compte_num']; ?></span></td>
                                            <td><?php echo htmlspecialchars($flux['libelle']); ?></td>
                                            <td class="text-end montant"><?php echo formatMontant($flux['total_debit']); ?></td>
                                            <td class="text-end montant"><?php echo formatMontant($flux['total_credit']); ?></td>
                                            <td class="text-end montant <?php echo $flux_net >= 0 ? 'flux-positif' : 'flux-negatif'; ?>">
                                                <?php echo formatMontant($flux_net); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-active">
                                            <td colspan="4" class="text-end fw-bold">TOTAL ACTIVITÉS D'EXPLOITATION :</td>
                                            <td class="text-end fw-bold <?php echo $total_flux_exploitation >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatMontant($total_flux_exploitation); ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <div>Aucune activité d'exploitation enregistrée</div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activités d'investissement -->
            <div class="col-12 mb-4">
                <div class="card shadow investissement-section">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>ACTIVITÉS D'INVESTISSEMENT</h5>
                        <span class="badge bg-light text-dark"><?php echo count($flux_investissement); ?> opérations</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">Compte</th>
                                        <th>Libellé</th>
                                        <th width="120" class="text-end">Débit</th>
                                        <th width="120" class="text-end">Crédit</th>
                                        <th width="150" class="text-end">Flux net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($flux_investissement) > 0): ?>
                                        <?php foreach ($flux_investissement as $flux): 
                                            $flux_net = $flux['total_credit'] - $flux['total_debit'];
                                        ?>
                                        <tr>
                                            <td><span class="badge bg-primary badge-compte"><?php echo $flux['compte_num']; ?></span></td>
                                            <td><?php echo htmlspecialchars($flux['libelle']); ?></td>
                                            <td class="text-end montant"><?php echo formatMontant($flux['total_debit']); ?></td>
                                            <td class="text-end montant"><?php echo formatMontant($flux['total_credit']); ?></td>
                                            <td class="text-end montant <?php echo $flux_net >= 0 ? 'flux-positif' : 'flux-negatif'; ?>">
                                                <?php echo formatMontant($flux_net); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-active">
                                            <td colspan="4" class="text-end fw-bold">TOTAL ACTIVITÉS D'INVESTISSEMENT :</td>
                                            <td class="text-end fw-bold <?php echo $total_flux_investissement >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatMontant($total_flux_investissement); ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <div>Aucune activité d'investissement enregistrée</div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activités de financement -->
            <div class="col-12 mb-4">
                <div class="card shadow financement-section">
                    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #6f42c1;">
                        <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>ACTIVITÉS DE FINANCEMENT</h5>
                        <span class="badge bg-light text-dark"><?php echo count($flux_financement); ?> opérations</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">Compte</th>
                                        <th>Libellé</th>
                                        <th width="120" class="text-end">Débit</th>
                                        <th width="120" class="text-end">Crédit</th>
                                        <th width="150" class="text-end">Flux net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($flux_financement) > 0): ?>
                                        <?php foreach ($flux_financement as $flux): 
                                            $flux_net = $flux['total_credit'] - $flux['total_debit'];
                                        ?>
                                        <tr>
                                            <td><span class="badge bg-primary badge-compte"><?php echo $flux['compte_num']; ?></span></td>
                                            <td><?php echo htmlspecialchars($flux['libelle']); ?></td>
                                            <td class="text-end montant"><?php echo formatMontant($flux['total_debit']); ?></td>
                                            <td class="text-end montant"><?php echo formatMontant($flux['total_credit']); ?></td>
                                            <td class="text-end montant <?php echo $flux_net >= 0 ? 'flux-positif' : 'flux-negatif'; ?>">
                                                <?php echo formatMontant($flux_net); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-active">
                                            <td colspan="4" class="text-end fw-bold">TOTAL ACTIVITÉS DE FINANCEMENT :</td>
                                            <td class="text-end fw-bold <?php echo $total_flux_financement >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatMontant($total_flux_financement); ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <div>Aucune activité de financement enregistrée</div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Synthèse des flux de trésorerie -->
            <div class="col-12">
                <div class="card shadow total-section">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>SYNTHÈSE DES FLUX DE TRÉSORERIE</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="table-light">
                                            <th colspan="2" class="text-center">FLUX DE TRÉSORERIE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td width="70%">Flux de trésorerie d'exploitation</td>
                                            <td class="text-end <?php echo $total_flux_exploitation >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatMontant($total_flux_exploitation); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Flux de trésorerie d'investissement</td>
                                            <td class="text-end <?php echo $total_flux_investissement >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatMontant($total_flux_investissement); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Flux de trésorerie de financement</td>
                                            <td class="text-end <?php echo $total_flux_financement >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatMontant($total_flux_financement); ?>
                                            </td>
                                        </tr>
                                        <tr class="table-active">
                                            <td><strong>FLUX NET DE TRÉSORERIE</strong></td>
                                            <td class="text-end fw-bold <?php echo $flux_net_tresorerie >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatMontant($flux_net_tresorerie); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="table-light">
                                            <th colspan="2" class="text-center">SOLDE DE TRÉSORERIE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td width="70%">Solde de trésorerie initial</td>
                                            <td class="text-end"><?php echo formatMontant($solde_initial); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Flux net de trésorerie de l'exercice</td>
                                            <td class="text-end <?php echo $flux_net_tresorerie >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatMontant($flux_net_tresorerie); ?>
                                            </td>
                                        </tr>
                                        <tr class="table-active">
                                            <td><strong>SOLDE DE TRÉSORERIE FINAL</strong></td>
                                            <td class="text-end fw-bold <?php echo $solde_final >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo formatMontant($solde_final); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-center small text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Solde final = Solde initial + Flux net de l'exercice
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Graphique de synthèse -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6><i class="fas fa-chart-pie me-2"></i>Répartition des flux de trésorerie</h6>
                                        <div class="row align-items-center">
                                            <div class="col-md-3 text-center">
                                                <div class="bg-success p-3 rounded-circle d-inline-block">
                                                    <i class="fas fa-industry fa-2x text-white"></i>
                                                </div>
                                                <div class="mt-2">
                                                    <strong>Exploitation</strong><br>
                                                    <span class="text-success"><?php 
                                                        $total_abs = abs($total_flux_exploitation) + abs($total_flux_investissement) + abs($total_flux_financement);
                                                        if ($total_abs > 0) {
                                                            echo round((abs($total_flux_exploitation) / $total_abs) * 100, 1) . '%';
                                                        } else {
                                                            echo '0%';
                                                        }
                                                    ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <div class="bg-primary p-3 rounded-circle d-inline-block">
                                                    <i class="fas fa-building fa-2x text-white"></i>
                                                </div>
                                                <div class="mt-2">
                                                    <strong>Investissement</strong><br>
                                                    <span class="text-primary"><?php 
                                                        if ($total_abs > 0) {
                                                            echo round((abs($total_flux_investissement) / $total_abs) * 100, 1) . '%';
                                                        } else {
                                                            echo '0%';
                                                        }
                                                    ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <div class="p-3 rounded-circle d-inline-block" style="background-color: #6f42c1;">
                                                    <i class="fas fa-hand-holding-usd fa-2x text-white"></i>
                                                </div>
                                                <div class="mt-2">
                                                    <strong>Financement</strong><br>
                                                    <span style="color: #6f42c1;"><?php 
                                                        if ($total_abs > 0) {
                                                            echo round((abs($total_flux_financement) / $total_abs) * 100, 1) . '%';
                                                        } else {
                                                            echo '0%';
                                                        }
                                                    ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <div class="p-3 rounded-circle d-inline-block <?php echo $flux_net_tresorerie >= 0 ? 'bg-warning' : 'bg-danger'; ?>">
                                                    <i class="fas fa-balance-scale fa-2x text-white"></i>
                                                </div>
                                                <div class="mt-2">
                                                    <strong>Flux net</strong><br>
                                                    <span class="<?php echo $flux_net_tresorerie >= 0 ? 'text-warning' : 'text-danger'; ?>">
                                                        <?php echo $flux_net_tresorerie >= 0 ? 'Excédent' : 'Déficit'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations complémentaires -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6><i class="fas fa-info-circle me-2"></i>Informations du rapport</h6>
                                <div class="row">
                                    <div class="col-sm-6"><small class="text-muted"><strong>Exercice:</strong> <?php echo $exercice_courant ? $exercice_courant['annee'] : 'Tous exercices'; ?></small></div>
                                    <div class="col-sm-6"><small class="text-muted"><strong>Date génération:</strong> <?php echo date('d/m/Y H:i'); ?></small></div>
                                    <div class="col-sm-6"><small class="text-muted"><strong>Total opérations:</strong> <?php echo count($flux_exploitation) + count($flux_investissement) + count($flux_financement); ?></small></div>
                                    <div class="col-sm-6"><small class="text-muted"><strong>Solde final:</strong> <?php echo formatMontant($solde_final); ?></small></div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i> Imprimer</button>
                                <button class="btn btn-success btn-sm" onclick="exportToExcel()"><i class="fas fa-file-excel me-1"></i> Excel</button>
                                <a href="tresorerie.php" class="btn btn-info btn-sm"><i class="fas fa-chart-line me-1"></i> Vue synthèse</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToExcel() {
            // Fonction d'export Excel basique
            let table = document.querySelector('.table');
            let html = table.outerHTML;
            let blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'tableau_flux_tresorerie.xls';
            a.click();
        }
    </script>
</body>
</html>
