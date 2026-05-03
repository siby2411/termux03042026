<?php
// /tableau_de_bord_complet.php
$page_title = "Tableau de Bord Financier & Support de Formation";
include_once 'includes/header.php'; // Inclusion du Header

// -------------------------------------------------------------
// BLOCK DE CALCUL ET DÉFINITION DES VARIABLES (CORPS PHP)
// -------------------------------------------------------------

// --- INCLUSIONS ET CONNEXION DB (Décommenter si nécessaire) ---
// include_once 'config/db.php'; 
// $db = (new Database())->getConnection();

// --- 1. FONCTION DE CALCUL DU SOLDE (Placeholder) ---
// Cette fonction devrait être utilisée pour récupérer les données réelles du GL.
function get_gl_solde($pdo, $code_compte, $date_fin) {
    // Si la DB est connectée, le code réel va ici.
    return (float) 0.00; // Placeholder
}
$N = 360; // Base de jours conventionnelle

// --- 2. AGRÉGATS CLÉS (Résultat des calculs du GL) ---
// Données brutes et agrégées
$ca = 120784.35;         // Ventes
$cdv = 11000.00;         // Coût des Ventes
$charges_fixes = 65000.00; // Total Charges d'Exploitation (641+627+681)
$achats_totaux = 114499.65; // Achats totaux pour le DPO (Stocks + CDV)

// Variables issues des calculs précédents
$resultat_net = 44784.35;
$frng = 275173.35;
$bfr = 63500.00;
$tn = 211673.35;

// Variables pour les ratios de rotation
$stocks_finaux = 103499.65;
$creances_clients = 35000.00;
$dettes_fournisseurs = 60000.00;

// Variables du Flux de Trésorerie
$amortissements_reels = 5000.00;
$ftf = 405389.00;
$fti = -180000.00;

// --- 3. CALCULS DE RENTABILITÉ ET DE FLUX ---
$marge_brute = $ca - $cdv;
$mscv = $marge_brute; 
$taux_mscv = $mscv / $ca;
$sr = $charges_fixes / $taux_mscv;
$point_mort_jours = ($sr / $ca) * $N;
$marge_securite = $ca - $sr;

// Calculs de Rotation
$dio = ($stocks_finaux / $cdv) * $N;
$dso = ($creances_clients / $ca) * $N;
$dpo = ($dettes_fournisseurs / $achats_totaux) * $N;
$ccc = $dio + $dso - $dpo;

// Calculs de Flux
$fte = $resultat_net + $amortissements_reels - $bfr; // FTE par méthode indirecte (simple)
$variation_tresorerie = $fte + $fti + $ftf;

// Données du Bilan (pour affichage)
$bilan_actif = [
    'Immobilisations (Net)' => 175000.00,
    'Stocks' => $stocks_finaux,
    'Créances Clients' => $creances_clients,
    'Trésorerie / Banque' => $tn, // Utilisons TN pour l'affichage cohérent du Bilan
];
$total_actif = array_sum($bilan_actif);

$bilan_passif = [
    'Capitaux Propres (Total)' => 300173.35, // Capital + RN
    'Emprunts LT' => 150000.00,
    'Dettes Fournisseurs' => $dettes_fournisseurs,
    'Dettes Fiscales & Sociales' => 15000.00,
];
$total_passif = array_sum($bilan_passif);
// -------------------------------------------------------------
// FIN DU BLOCK DE CALCUL
// -------------------------------------------------------------

?>

<h1 class="mt-4 text-center"><i class="fas fa-cubes me-2"></i> Tableau de Bord Financier Complet (Support de Formation)</h1>
<p class="text-muted text-center">Analyse exhaustive des performances, de la structure et de la liquidité de l'entreprise.</p>
<hr class="mb-5">

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg border-primary border-3 h-100">
            <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-chart-bar me-2"></i> Compte de Résultat Simplifié (Annuel)</div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tr><th>Ventes de Marchandises</th><td class="text-end fw-bold"><?= number_format($ca, 2, ',', ' ') ?> €</td></tr>
                    <tr><th>Coûts Variables (CDV)</th><td class="text-end text-danger"><?= number_format($cdv, 2, ',', ' ') ?> €</td></tr>
                    <tr class="table-info"><th>MARGE SUR COÛTS VARIABLES (MSCV)</th><td class="text-end fw-bold"><?= number_format($mscv, 2, ',', ' ') ?> €</td></tr>
                    <tr><th>Charges Fixes (OPEX, Amort.)</th><td class="text-end text-danger"><?= number_format($charges_fixes, 2, ',', ' ') ?> €</td></tr>
                    <tr class="table-success"><th>RÉSULTAT NET</th><td class="text-end fw-bold"><?= number_format($resultat_net, 2, ',', ' ') ?> €</td></tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg border-success border-3 h-100">
            <div class="card-header bg-success text-white fw-bold"><i class="fas fa-chart-line me-2"></i> Ratios de Rentabilité et Seuil de Rentabilité</div>
            <div class="card-body">
                <table class="table table-sm table-bordered">
                    <thead><tr class="table-light"><th>Ratio</th><th>Valeur</th><th>Formule</th></tr></thead>
                    <tbody>
                        <tr><td>Taux de MSCV</td><td><?= number_format($taux_mscv * 100, 2, ',', ' ') ?> %</td><td>$$\text{Taux MSCV} = \frac{\text{MSCV}}{\text{CA}}$$</td></tr>
                        <tr><td>**Seuil de Rentabilité (SR)**</td><td>**<?= number_format($sr, 2, ',', ' ') ?> €**</td><td>$$\text{SR} = \frac{\text{Charges Fixes}}{\text{Taux MSCV}}$$</td></tr>
                        <tr><td>**Point Mort (Jours)**</td><td>**<?= number_format($point_mort_jours, 0, ',', ' ') ?> j**</td><td>$$\text{PM} = \frac{\text{SR}}{\text{CA}} \times 360$$</td></tr>
                        <tr><td>Marge de Sécurité</td><td><?= number_format($marge_securite, 2, ',', ' ') ?> €</td><td>$$\text{MS} = \text{CA} - \text{SR}$$</td></tr>
                    </tbody>
                </table>
                <p class="small mt-3">**Conclusion :** L'entreprise devient rentable après **<?= number_format($point_mort_jours, 0, ',', ' ') ?> jours** de ventes. Notre Marge de Sécurité est positive.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h2 class="mb-4 text-secondary"><i class="fas fa-balance-scale me-2"></i> Bilan Annuel (Présentation Formelle)</h2>
    </div>
    <div class="col-lg-6">
        <div class="card shadow border-secondary h-100">
            <div class="card-header bg-secondary text-white fw-bold">ACTIF (Emplois)</div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <?php foreach($bilan_actif as $libelle => $montant): ?>
                    <tr><th><?= $libelle ?></th><td class="text-end"><?= number_format($montant, 2, ',', ' ') ?> €</td></tr>
                    <?php endforeach; ?>
                    <tr class="table-dark"><th>TOTAL ACTIF</th><td class="text-end fw-bold"><?= number_format($total_actif, 2, ',', ' ') ?> €</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow border-secondary h-100">
            <div class="card-header bg-secondary text-white fw-bold">PASSIF (Ressources)</div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <?php foreach($bilan_passif as $libelle => $montant): ?>
                    <tr><th><?= $libelle ?></th><td class="text-end"><?= number_format($montant, 2, ',', ' ') ?> €</td></tr>
                    <?php endforeach; ?>
                    <tr class="table-dark"><th>TOTAL PASSIF</th><td class="text-end fw-bold"><?= number_format($total_passif, 2, ',', ' ') ?> €</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <h2 class="mb-4 text-warning"><i class="fas fa-cogs me-2"></i> Ratios Financiers Stratégiques (Structure & Exploitation)</h2>
    <div class="col-12">
    
        <table class="table table-bordered table-hover">
            <thead class="table-warning">
                <tr><th>Ratio</th><th>Valeur Calculée</th><th>Formule (avec Variables)</th><th>Analyse / Seuil</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="table-secondary fw-bold">A. Ratios de Structure et Liquidité</td>
                </tr>
                <tr>
                    <td>**FRNG**</td>
                    <td><?= number_format($frng, 0, ',', ' ') ?> €</td>
                    <td>$$\text{FRNG} = (\text{Capitaux Propres} + \text{Dettes LT}) - \text{Immobilisations}$$</td>
                    <td>**Solide.** L'équilibre LT est assuré ($\text{FRNG} > 0$).</td>
                </tr>
                <tr>
                    <td>**BFR**</td>
                    <td><?= number_format($bfr, 0, ',', ' ') ?> €</td>
                    <td>$$\text{BFR} = (\text{Stocks} + \text{Clients}) - (\text{Fournisseurs} + \text{Dettes Fisc.})$$</td>
                    <td>**Besoin important.** Principalement dû au Stock élevé.</td>
                </tr>
                <tr>
                    <td>**Trésorerie Nette (TN)**</td>
                    <td><?= number_format($tn, 0, ',', ' ') ?> €</td>
                    <td>$$\text{TN} = \text{FRNG} - \text{BFR}$$</td>
                    <td>**Forte Liquidité.** $\text{TN} > 0$ garantit la solvabilité à court terme.</td>
                </tr>
                <tr>
                    <td colspan="4" class="table-secondary fw-bold">B. Ratios d'Exploitation et Cycle de Cash</td>
                </tr>
                <tr>
                    <td>Rotation Stocks (DIO)</td>
                    <td><?= number_format($dio, 0, ',', ' ') ?> jours</td>
                    <td>$$\text{DIO} = \frac{\text{Stocks}}{\text{CDV}} \times 360$$</td>
                    <td class="text-danger">**<?= number_format($dio, 0, ',', ' ') ?> jours (Critique).** Urgence de réduire le stock.</td>
                </tr>
                <tr>
                    <td>Rotation Clients (DSO)</td>
                    <td><?= number_format($dso, 0, ',', ' ') ?> jours</td>
                    <td>$$\text{DSO} = \frac{\text{Clients}}{\text{CA}} \times 360$$</td>
                    <td>**<?= number_format($dso, 0, ',', ' ') ?> jours.** Trop long ; affecte négativement le BFR.</td>
                </tr>
                <tr>
                    <td>Rotation Fournisseurs (DPO)</td>
                    <td><?= number_format($dpo, 0, ',', ' ') ?> jours</td>
                    <td>$$\text{DPO} = \frac{\text{Fournisseurs}}{\text{Achats}} \times 360$$</td>
                    <td>**<?= number_format($dpo, 0, ',', ' ') ?> jours.** Excellent délai de paiement.</td>
                </tr>
                <tr>
                    <td>**Cycle de Conversion Trésorerie (CCC)**</td>
                    <td><?= number_format($ccc, 0, ',', ' ') ?> jours</td>
                    <td>$$\text{CCC} = \text{DIO} + \text{DSO} - \text{DPO}$$</td>
                    <td class="text-danger">**<?= number_format($ccc, 0, ',', ' ') ?> jours (Inacceptable).** Dû principalement au Stock.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h2 class="mb-4 text-info"><i class="fas fa-exchange-alt me-2"></i> Tableau des Flux de Trésorerie (Méthode Indirecte)</h2>
        <p class="text-muted">Analyse du cash-flow basée sur le Résultat Net et la variation des éléments non monétaires.</p>
        
        <table class="table table-bordered table-striped">
            <thead class="table-info">
                <tr><th>Type de Flux</th><th>Détail / Agrégat</th><th>Montant (€)</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="4" class="fw-bold align-middle bg-light">A. Activités Opérationnelles (Exploitation)</td>
                    <td>Résultat Net</td><td class="text-end"><?= number_format($resultat_net, 2, ',', ' ') ?></td>
                </tr>
                <tr>
                    <td>(+) Amortissements (Non décaissable)</td><td class="text-end"><?= number_format($amortissements_reels, 2, ',', ' ') ?></td>
                </tr>
                <tr>
                    <td>(-) Variation du BFR (Besoin de financement)</td><td class="text-end text-danger">(<?= number_format($bfr, 2, ',', ' ') ?>)</td>
                </tr>
                <tr class="table-warning">
                    <td class="fw-bold">TOTAL FTE (Cash Net Opérationnel)</td><td class="text-end fw-bold"><?= number_format($fte, 2, ',', ' ') ?></td>
                </tr>
                
                <tr>
                    <td rowspan="2" class="fw-bold align-middle bg-light">B. Activités d'Investissement</td>
                    <td>Acquisitions d'Immobilisations</td><td class="text-end text-danger">(<?= number_format(abs($fti), 2, ',', ' ') ?>)</td>
                </tr>
                <tr class="table-danger">
                    <td class="fw-bold">TOTAL FTI</td><td class="text-end fw-bold text-danger"><?= number_format($fti, 2, ',', ' ') ?></td>
                </tr>

                <tr>
                    <td rowspan="2" class="fw-bold align-middle bg-light">C. Activités de Financement</td>
                    <td>Apports Capital & Emprunts LT</td><td class="text-end"><?= number_format($ftf, 2, ',', ' ') ?></td>
                </tr>
                <tr class="table-success">
                    <td class="fw-bold">TOTAL FTF</td><td class="text-end fw-bold"><?= number_format($ftf, 2, ',', ' ') ?></td>
                </tr>
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <th>VARIATION NETTE DE TRÉSORERIE (A + B + C)</th>
                    <th>Égal à $\text{TN}_{\text{finale}} - \text{TN}_{\text{initiale}}$</th>
                    <th class="text-end"><?= number_format($variation_tresorerie, 2, ',', ' ') ?> €</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
