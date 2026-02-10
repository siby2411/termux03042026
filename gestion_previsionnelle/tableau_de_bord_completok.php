<?php
// /tableau_de_bord_complet.php
$page_title = "Tableau de Bord Financier & Support de Formation";
include_once 'includes/header.php'; // Inclusion du Header
// ... (Les variables de calcul $bilan, $cr, $frng, $bfr, $tn, $resultat_net, $fte, $dio, $dso, $dpo, $ccc, $sr, etc. sont supposées être calculées ici) ...
// NOTE: Les calculs sont basés sur les sections 1 et 2 de la réponse précédente.

// Agrégats pour l'interface
$marge_brute = 109784.35;
$charges_fixes = 65000.00;
$mscv = 109784.35;
$taux_mscv = 0.9089;
$sr = 71519.41;
$point_mort_jours = 213.29;
$marge_securite = 49264.94;

$total_actif = 359354.00; // Actif réel du GL
$total_passif = 525173.35; // Passif réel du GL

// Données du Bilan (pour affichage)
$bilan_actif = [
    'Immobilisations (Net)' => 175000.00,
    'Stocks' => 103499.65,
    'Créances Clients' => 35000.00,
    'Trésorerie / Banque' => 45854.35,
];
$bilan_passif = [
    'Capitaux Propres (Total)' => 300173.35,
    'Emprunts LT' => 150000.00,
    'Dettes Fournisseurs' => 60000.00,
    'Dettes Fiscales & Sociales' => 15000.00,
];

// Données du Flux (pour affichage)
$ftf = 405389.00; // 255389 + 150000
$fti = -180000.00;
$variation_tresorerie = 211673.35;
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
                <p class="small mt-3">**Conclusion :** L'entreprise devient rentable après **214 jours** de ventes ($71\,519\text{ €}$). Notre Marge de Sécurité est positive, assurant une protection contre une baisse du CA.</p>
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
                    <td>$$\text{BFR} = \text{Actif Circulant Expl.} - \text{Passif Circulant Expl.}$$</td>
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
                    <td class="text-danger">**3393 jours (Critique).** Urgence de réduire le stock ou réévaluer le CDV.</td>
                </tr>
                <tr>
                    <td>Rotation Clients (DSO)</td>
                    <td><?= number_format($dso, 0, ',', ' ') ?> jours</td>
                    <td>$$\text{DSO} = \frac{\text{Clients}}{\text{CA}} \times 360$$</td>
                    <td>**104 jours.** Trop long ; affecte négativement le BFR.</td>
                </tr>
                <tr>
                    <td>Rotation Fournisseurs (DPO)</td>
                    <td><?= number_format($dpo, 0, ',', ' ') ?> jours</td>
                    <td>$$\text{DPO} = \frac{\text{Fournisseurs}}{\text{Achats}} \times 360$$</td>
                    <td>**189 jours.** Excellent délai de paiement ; avantage majeur.</td>
                </tr>
                <tr>
                    <td>**Cycle de Conversion Trésorerie (CCC)**</td>
                    <td><?= number_format($ccc, 0, ',', ' ') ?> jours</td>
                    <td>$$\text{CCC} = \text{DIO} + \text{DSO} - \text{DPO}$$</td>
                    <td class="text-danger">**3309 jours (Inacceptable).** Principalement causé par la mauvaise rotation du stock.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h2 class="mb-4 text-info"><i class="fas fa-exchange-alt me-2"></i> Tableau des Flux de Trésorerie (Cash-Flow)</h2>
        <p class="text-muted">Analyse de la source et de l'utilisation du cash (Méthode Indirecte, basée sur la variation du GL).</p>
        
        <table class="table table-bordered table-striped">
            <thead class="table-info">
                <tr><th>Type de Flux</th><th>Détail / Agrégat</th><th>Montant (€)</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="4" class="fw-bold align-middle">A. Flux d'Exploitation (FTE)</td>
                    <td>Résultat Net</td><td class="text-end"><?= number_format($resultat_net, 2, ',', ' ') ?></td>
                </tr>
                <tr>
                    <td>(+) Amortissements (Non décaissable)</td><td class="text-end">5 000,00</td>
                </tr>
                <tr>
                    <td>(-) Variation du BFR (Besoin de financement)</td><td class="text-end text-danger">(<?= number_format($bfr, 2, ',', ' ') ?>)</td>
                </tr>
                <tr>
                    <td class="fw-bold">FTE Net (Cash généré par l'activité)</td><td class="text-end text-danger fw-bold"><?= number_format($fte, 2, ',', ' ') ?></td>
                </tr>
                <tr>
                    <td rowspan="2" class="fw-bold align-middle">B. Flux d'Investissement (FTI)</td>
                    <td>Acquisitions Immobilisations</td><td class="text-end text-danger">(180 000,00)</td>
                </tr>
                <tr>
                    <td class="fw-bold">FTI Net</td><td class="text-end text-danger fw-bold"><?= number_format($fti, 2, ',', ' ') ?></td>
                </tr>
                <tr>
                    <td rowspan="2" class="fw-bold align-middle">C. Flux de Financement (FTF)</td>
                    <td>Apports Capital & Emprunts LT</td><td class="text-end"><?= number_format($ftf, 2, ',', ' ') ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">FTF Net</td><td class="text-end fw-bold"><?= number_format($ftf, 2, ',', ' ') ?></td>
                </tr>
            </tbody>
            <tfoot class="table-dark">
                <tr><th>VARIATION NETTE DE TRÉSORERIE (A + B + C)</th><th></th><th class="text-end"><?= number_format($variation_tresorerie, 2, ',', ' ') ?> €</th></tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
