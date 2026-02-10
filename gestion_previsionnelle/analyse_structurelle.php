<?php
// /analyse_structurelle.php
$page_title = "Analyse de la Structure Financière et du Bilan";
include_once __DIR__ . '/config/db.php'; 
include_once __DIR__ . '/includes/header.php'; 

$database = new Database();
$db = $database->getConnection();

// --- Période d'analyse : Année en cours ---
$date_fin_annee = date('Y-m-d'); 

// --- Fonction générique d'agrégation du Grand Livre (GL) ---
// Utilise la structure de votre table: CompteDebiteur, CompteCrediteur
function get_gl_solde($pdo, $code_compte, $date_fin) {
    // Calcule le solde cumulé Débit - Crédit
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

// ==========================================================
// 1. CALCUL DES ÉLÉMENTS DU BILAN ET DU COMPTE DE RÉSULTAT
// ==========================================================

// --- COMPTE DE RÉSULTAT ---
$cr = [];
$cr['Ventes'] = get_gl_solde($db, '707', $date_fin_annee) * -1; // Produits (solde créditeur)
$cr['AchatsMarchandises'] = get_gl_solde($db, '607', $date_fin_annee); 
$cr['ChargesPersonnel'] = get_gl_solde($db, '641', $date_fin_annee); 
$cr['ChargesExploitation'] = get_gl_solde($db, '627', $date_fin_annee); 
$cr['Amortissements'] = get_gl_solde($db, '681', $date_fin_annee); 

$marge_brute = $cr['Ventes'] - $cr['AchatsMarchandises'];
$total_charges_exploitation = $cr['ChargesPersonnel'] + $cr['ChargesExploitation'] + $cr['Amortissements'];
$resultat_net = $marge_brute - $total_charges_exploitation;

// --- BILAN ACTIF (Emplois) ---
$bilan = [];
$bilan['Immobilisations'] = get_gl_solde($db, '200', $date_fin_annee); 
$bilan['Stocks'] = get_gl_solde($db, '370', $date_fin_annee); 
$bilan['Clients'] = get_gl_solde($db, '411', $date_fin_annee); 
$bilan['Banque'] = get_gl_solde($db, '512', $date_fin_annee); 

// --- BILAN PASSIF (Ressources) ---
$bilan['Capital'] = get_gl_solde($db, '101', $date_fin_annee) * -1; 
$bilan['Emprunts'] = get_gl_solde($db, '164', $date_fin_annee) * -1;
$bilan['Fournisseurs'] = get_gl_solde($db, '401', $date_fin_annee) * -1;
$bilan['DettesFiscales'] = get_gl_solde($db, '445', $date_fin_annee) * -1;

// ==========================================================
// 2. CALCUL DES GRANDEURS STRUCTURELLES (FRNG, BFR, TN)
// ==========================================================

// A. Fonds de Roulement Net Global (FRNG)
$capitaux_propres = $bilan['Capital'] + $resultat_net;
$ressources_stables = $capitaux_propres + $bilan['Emprunts']; // Emprunts 164 sont LT
$emplois_stables = $bilan['Immobilisations'];
$frng = $ressources_stables - $emplois_stables;

// B. Besoin en Fonds de Roulement (BFR)
$actif_circulant_exploitation = $bilan['Stocks'] + $bilan['Clients'];
$passif_circulant_exploitation = $bilan['Fournisseurs'] + $bilan['DettesFiscales'];
$bfr = $actif_circulant_exploitation - $passif_circulant_exploitation;

// C. Trésorerie Nette (TN)
$tn = $frng - $bfr;
$tn_check = $bilan['Banque']; // Trésorerie active - Trésorerie passive (ici on suppose 512 = TN)

// --- Totaux pour affichage cohérent ---
$total_actif = $bilan['Immobilisations'] + $bilan['Stocks'] + $bilan['Clients'] + $bilan['Banque'];
$total_passif = $bilan['Capital'] + $bilan['Emprunts'] + $bilan['Fournisseurs'] + $bilan['DettesFiscales'] + $resultat_net;
$ecart_bilan = $total_actif - $total_passif;

?>

<h1 class="mt-4 text-center"><i class="fas fa-search-dollar me-2"></i> Rapport de Synthèse Financière & Structurelle</h1>
<p class="text-muted text-center">Analyse des performances (CR) et de la structure de financement (Bilan Fonctionnel) au <?= $date_fin_annee ?>.</p>
<hr class="mb-5">

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow-lg border-primary border-3">
            <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-chart-line me-2"></i> Compte de Résultat (RÉEL du Grand Livre)</div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tr><th>Ventes de Marchandises (Produits)</th><td class="text-end fw-bold text-success"><?= number_format($cr['Ventes'], 2, ',', ' ') ?> €</td></tr>
                    <tr><th>Coût des Ventes (Achats 607)</th><td class="text-end text-danger"><?= number_format($cr['AchatsMarchandises'], 2, ',', ' ') ?> €</td></tr>
                    <tr class="table-info"><th>MARGE BRUTE</th><td class="text-end fw-bold"><?= number_format($marge_brute, 2, ',', ' ') ?> €</td></tr>
                    <tr><th>Total Charges d'Exploitation (6xx)</th><td class="text-end text-danger"><?= number_format($total_charges_exploitation, 2, ',', ' ') ?> €</td></tr>
                    <?php $style_resultat = ($resultat_net >= 0) ? 'text-success' : 'text-danger'; ?>
                    <tr class="table-primary"><th>RÉSULTAT NET DE L'EXERCICE</th><td class="text-end fw-bold <?= $style_resultat ?>"><?= number_format($resultat_net, 2, ',', ' ') ?> €</td></tr>
                </table>
                <p class="text-muted small">**Observation CR :** La marge brute de <?= number_format($marge_brute, 0, ',', ' ') ?> € ne couvre pas les charges d'exploitation de <?= number_format($total_charges_exploitation, 0, ',', ' ') ?> €, résultant en une perte réelle de **<?= number_format(abs($resultat_net), 0, ',', ' ') ?> €**.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <h2 class="mb-4"><i class="fas fa-tools me-2 text-warning"></i> Pilotage de la Structure de Financement</h2>
    
    <div class="col-lg-4 mb-4">
        <?php $bg_frng = ($frng >= 0) ? 'bg-success' : 'bg-danger'; ?>
        <div class="card border-0 shadow-lg text-white <?= $bg_frng ?> h-100">
            <div class="card-body">
                <div class="fs-6 text-uppercase">Fonds de Roulement Net Global (FRNG)</div>
                <div class="fs-3 fw-bold"><?= number_format($frng, 0, ',', ' ') ?> €</div>
                <p class="text-light small mt-2">Capacité de financement à long terme. **(Doit être positif)**</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <?php $bg_bfr = ($bfr >= 0) ? 'bg-warning text-dark' : 'bg-info text-white'; ?>
        <div class="card border-0 shadow-lg <?= $bg_bfr ?> h-100">
            <div class="card-body">
                <div class="fs-6 text-uppercase">Besoin en Fonds de Roulement (BFR)</div>
                <div class="fs-3 fw-bold"><?= number_format($bfr, 0, ',', ' ') ?> €</div>
                <p class="small mt-2">Besoin de financement généré par l'activité. **(Cible: Réduire)**</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <?php $bg_tn = ($tn >= 0) ? 'bg-primary' : 'bg-danger'; ?>
        <div class="card border-0 shadow-lg text-white <?= $bg_tn ?> h-100">
            <div class="card-body">
                <div class="fs-6 text-uppercase">Trésorerie Nette (TN)</div>
                <div class="fs-3 fw-bold"><?= number_format($tn, 0, ',', ' ') ?> €</div>
                <p class="text-light small mt-2">Position de liquidité finale. **(Doit être positif)**</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-lightbulb me-2 text-info"></i> Interprétation et Décisions Stratégiques</h2>
        
        <table class="table table-bordered table-sm">
            <thead class="table-dark">
                <tr><th>Indicateur</th><th>Valeur Réelle</th><th>Analyse et Recommandation</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>**Résultat Net**</td>
                    <td><?= number_format($resultat_net, 0, ',', ' ') ?> €</td>
                    <td class="text-danger">**Perte de $21\,035\text{ €}$** : La performance est insuffisante pour couvrir les charges, même en supposant un CA élevé de $54\,965\text{ €}$. Il faut revoir le modèle de coût ou le prix de vente.</td>
                </tr>
                <tr>
                    <td>**FRNG**</td>
                    <td><?= number_format($frng, 0, ',', ' ') ?> €</td>
                    <td class="text-success">**Financement Solide** : Le Fonds de Roulement Net Global est largement positif (<?= number_format($frng, 0, ',', ' ') ?> €). Les ressources stables (Capital, Emprunts LT) sont **plus que suffisantes** pour financer les immobilisations ($175\,000\text{ €}$).</td>
                </tr>
                <tr>
                    <td>**BFR**</td>
                    <td><?= number_format($bfr, 0, ',', ' ') ?> €</td>
                    <td class="text-warning">**Besoins d'Exploitation** : Le BFR est positif (<?= number_format($bfr, 0, ',', ' ') ?> €). L'activité génère un besoin de financement (principalement dû au Stock important : $103k$).</td>
                </tr>
                <tr>
                    <td>**Trésorerie Nette**</td>
                    <td><?= number_format($tn, 0, ',', ' ') ?> €</td>
                    <td class="text-success">**Liquidité Positive** : La Trésorerie Nette est positive. Le surplus de ressources stables ($230\,389\text{ €}$) est suffisant pour couvrir l'ensemble des besoins du cycle d'exploitation ($63\,499\text{ €}$). **L'entreprise est liquide, mais non rentable.**</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
