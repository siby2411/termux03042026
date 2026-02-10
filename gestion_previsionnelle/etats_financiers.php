<?php
// /etats_financiers.php
$page_title = "Analyse des États Financiers (Comptes Annuels)";
include_once __DIR__ . '/config/db.php'; 
include_once __DIR__ . '/includes/header.php'; 

$database = new Database();
$db = $database->getConnection();

// --- Période d'analyse : Année en cours ---
$date_debut_annee = date('Y-01-01');
$date_fin_annee = date('Y-m-d'); 

// --- Fonction générique d'agrégation du Grand Livre (GL) ---
// ADAPTÉ À VOTRE STRUCTURE GRANDLIVRE (CompteDebiteur, CompteCrediteur)
function get_gl_solde($pdo, $code_compte, $date_fin) {
    // Calcule le solde cumulé Débit - Crédit pour un compte jusqu'à une date donnée
    // Les comptes d'ACTIF et CHARGE augmentent au DEBIT.
    // Les comptes de PASSIF et PRODUIT augmentent au CREDIT (Solde Créditeur).
    $sql = "
        SELECT 
            COALESCE(SUM(CASE WHEN CompteDebiteur = :code THEN Montant ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN CompteCrediteur = :code THEN Montant ELSE 0 END), 0)
        FROM GrandLivre
        WHERE DateComptable <= :date_fin
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':code' => $code_compte, ':date_fin' => $date_fin]);
    return (float)$stmt->fetchColumn();
}

// --- Calculs pour le Compte de Résultat (Cumul YTD) ---

$cr = [];
// Les comptes de PRODUIT ont un solde CREDITEUR (négatif dans notre fonction get_gl_solde) -> * -1
$cr['Ventes'] = get_gl_solde($db, '707', $date_fin_annee) * -1; 
// Les comptes de CHARGE ont un solde DEBITEUR (positif)
$cr['AchatsMarchandises'] = get_gl_solde($db, '607', $date_fin_annee); 
$cr['ChargesPersonnel'] = get_gl_solde($db, '641', $date_fin_annee); 
$cr['ChargesExploitation'] = get_gl_solde($db, '627', $date_fin_annee); 
$cr['Amortissements'] = get_gl_solde($db, '681', $date_fin_annee); 

// --- CALCULS DU BILAN (Solde au jour J) ---
$bilan = [];
// Actif (Débit - Crédit)
$bilan['Immobilisations'] = get_gl_solde($db, '200', $date_fin_annee); 
$bilan['Stocks'] = get_gl_solde($db, '370', $date_fin_annee); 
$bilan['Clients'] = get_gl_solde($db, '411', $date_fin_annee); 
$bilan['Banque'] = get_gl_solde($db, '512', $date_fin_annee); 

// Passif (Crédit - Débit) -> * -1
$bilan['Capital'] = get_gl_solde($db, '101', $date_fin_annee) * -1; 
$bilan['Emprunts'] = get_gl_solde($db, '164', $date_fin_annee) * -1;
$bilan['Fournisseurs'] = get_gl_solde($db, '401', $date_fin_annee) * -1;
$bilan['DettesFiscales'] = get_gl_solde($db, '445', $date_fin_annee) * -1;

// Calcul du Résultat
$marge_brute = $cr['Ventes'] - $cr['AchatsMarchandises'];
$resultat_net = $marge_brute - ($cr['ChargesPersonnel'] + $cr['ChargesExploitation'] + $cr['Amortissements']);

// --- Agrégation Bilan ---
$total_actif = $bilan['Immobilisations'] + $bilan['Stocks'] + $bilan['Clients'] + $bilan['Banque'];
$total_passif = $bilan['Capital'] + $bilan['Emprunts'] + $bilan['Fournisseurs'] + $bilan['DettesFiscales'] + $resultat_net; // Le Résultat Net est un élément du Passif

// Si le bilan est déséquilibré
$ecart_bilan = $total_actif - $total_passif;
?>

<h1 class="mt-4 text-center"><i class="fas fa-file-invoice-dollar me-2"></i> États Financiers Annuels (Pro-Forma)</h1>
<p class="text-muted text-center">Basé sur les données du **Grand Livre** du <?= $date_debut_annee ?> au <?= $date_fin_annee ?>.</p>
<hr class="mb-5">

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg h-100 border-primary border-3">
            <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-chart-line me-2"></i> Compte de Résultat Simplifié (YTD)</div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <thead><tr class="table-secondary"><th colspan="2">Produits et Charges d'Exploitation</th></tr></thead>
                    <tbody>
                        <tr><th>Ventes de Marchandises (707)</th><td class="text-end fw-bold text-success"><?= number_format($cr['Ventes'], 2, ',', ' ') ?> €</td></tr>
                        <tr><th>Coût des Ventes (607)</th><td class="text-end text-danger"><?= number_format($cr['AchatsMarchandises'], 2, ',', ' ') ?> €</td></tr>
                        <tr class="table-info"><th>MARGE BRUTE</th><td class="text-end fw-bold"><?= number_format($marge_brute, 2, ',', ' ') ?> €</td></tr>
                        <tr><th>Charges de Personnel (641)</th><td class="text-end text-danger"><?= number_format($cr['ChargesPersonnel'], 2, ',', ' ') ?> €</td></tr>
                        <tr><th>Autres Charges Exploitation (627)</th><td class="text-end text-danger"><?= number_format($cr['ChargesExploitation'], 2, ',', ' ') ?> €</td></tr>
                        <tr><th>Dotations aux Amortissements (681)</th><td class="text-end text-danger"><?= number_format($cr['Amortissements'], 2, ',', ' ') ?> €</td></tr>
                    </tbody>
                    <tfoot>
                        <?php $style_resultat = ($resultat_net >= 0) ? 'text-success' : 'text-danger'; ?>
                        <tr class="table-primary"><th>RÉSULTAT NET (Avant Impôts)</th><td class="text-end fw-bold <?= $style_resultat ?>"><?= number_format($resultat_net, 2, ',', ' ') ?> €</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg h-100 border-success border-3">
            <div class="card-header bg-success text-white fw-bold"><i class="fas fa-balance-scale me-2"></i> Bilan (au <?= $date_fin_annee ?>)</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <table class="table table-sm table-borderless">
                            <thead><tr class="table-secondary"><th colspan="2">ACTIF (Emplois)</th></tr></thead>
                            <tbody>
                                <tr><th>Immobilisations (200)</th><td class="text-end"><?= number_format($bilan['Immobilisations'], 2, ',', ' ') ?> €</td></tr>
                                <tr><th>Stocks (370)</th><td class="text-end"><?= number_format($bilan['Stocks'], 2, ',', ' ') ?> €</td></tr>
                                <tr><th>Créances Clients (411)</th><td class="text-end"><?= number_format($bilan['Clients'], 2, ',', ' ') ?> €</td></tr>
                                <tr><th>Trésorerie / Banque (512)</th><td class="text-end"><?= number_format($bilan['Banque'], 2, ',', ' ') ?> €</td></tr>
                                <tr class="table-success"><th>TOTAL ACTIF</th><td class="text-end fw-bold"><?= number_format($total_actif, 2, ',', ' ') ?> €</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-sm table-borderless">
                            <thead><tr class="table-secondary"><th colspan="2">PASSIF (Ressources)</th></tr></thead>
                            <tbody>
                                <tr><th>Capital Social (101)</th><td class="text-end"><?= number_format($bilan['Capital'], 2, ',', ' ') ?> €</td></tr>
                                <tr><th>Emprunts (164)</th><td class="text-end"><?= number_format($bilan['Emprunts'], 2, ',', ' ') ?> €</td></tr>
                                <tr><th>Dettes Fournisseurs (401)</th><td class="text-end"><?= number_format($bilan['Fournisseurs'], 2, ',', ' ') ?> €</td></tr>
                                <tr><th>Dettes Fiscales & Sociales (445)</th><td class="text-end"><?= number_format($bilan['DettesFiscales'], 2, ',', ' ') ?> €</td></tr>
                                <tr class="<?= $style_resultat ?>"><th>Résultat Net</th><td class="text-end fw-bold"><?= number_format($resultat_net, 2, ',', ' ') ?> €</td></tr>
                                <tr class="table-success"><th>TOTAL PASSIF</th><td class="text-end fw-bold"><?= number_format($total_passif, 2, ',', ' ') ?> €</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if (abs($ecart_bilan) > 0.01): ?>
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle"></i> **Déséquilibre du Bilan:** Écart de <?= number_format($ecart_bilan, 2, ',', ' ') ?> €. **Vérifiez les écritures du Grand Livre.**
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-book-open me-2 text-info"></i> Perspectives d'Analyse (Grand Livre)</h2>
        <div class="alert alert-info">
            <p>La mise en place du **Grand Livre** ouvre la porte à des analyses financières sophistiquées :</p>
            <ul>
                <li>**Analyse de la Liquidité** : Calcul du *Ratio de Liquidité Générale* ($\text{Actif Circulant} / \text{Passif Circulant}$) et du *Ratio de Trésorerie* (Actif très liquide / Dettes CT).</li>
                <li>**Analyse de la Solvabilité** : Mesure de la capacité à rembourser les dettes à long terme (via l'Emprunt 164).</li>
                <li>**Calcul du BFR** : Le **Besoin en Fonds de Roulement** (Stocks + Clients - Fournisseurs) est désormais calculable pour piloter les besoins de financement à court terme.</li>
            </ul>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
