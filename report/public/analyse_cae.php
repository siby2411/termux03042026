<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Analyse CAE - Comptabilité Analytique d'Exploitation";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupération des données CG
$ca_ventes = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$achats = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$salaires = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 641")->fetchColumn();
$services_externes = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 613")->fetchColumn();
$dotations = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 681")->fetchColumn();
$provisions = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE type_ecriture = 'PROVISION'")->fetchColumn();
$impots = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 695")->fetchColumn();
$charges_financieres = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 671")->fetchColumn();

// Calcul charges totales CG
$total_charges_cg = $achats + $salaires + $services_externes + $dotations + $provisions + $impots + $charges_financieres;
$resultat_cg = $ca_ventes - $total_charges_cg;

// Charges non incorporables
$charges_non_incorporables = [
    'dotations_excedentaires' => 100000,
    'provisions' => $provisions,
    'impots_resultats' => $impots,
];
$total_non_incorporables = array_sum($charges_non_incorporables);

// Charges supplétives (non dans CG mais prises en CAE)
$charges_suppletives = [
    'remuneration_dirigeant' => 300000,
    'remuneration_capital' => 200000,
];
$total_suppletives = array_sum($charges_suppletives);

// Résultat CAE
$resultat_cae = $resultat_cg + $total_non_incorporables - $total_suppletives;

// Calcul CMUP
$cmup = $pdo->query("
    SELECT article_id, 
           SUM(quantite * prix_unitaire) / SUM(quantite) as cmup
    FROM MOUVEMENTS_STOCK 
    WHERE type_mouvement = 'ENTREE'
    GROUP BY article_id
")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Analyse CAE - Société Générale</h5>
                <small>Passage du résultat Comptabilité Générale à la Comptabilité Analytique</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 PRINCIPE FONDAMENTAL :</strong><br>
                    Résultat CAE = Résultat CG + Charges non incorporables - Charges supplétives
                </div>

                <!-- Partie 1: Comptabilité Générale -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">1. COMPTABILITÉ GÉNÉRALE (CG)</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>📈 PRODUITS</h6>
                                <table class="table table-sm">
                                    <tr><th>Rubrique</th><th class="text-end">Montant (F)</th></tr>
                                    <tr><td>Ventes de marchandises</td><td class="text-end text-success"><?= number_format($ca_ventes, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>📉 CHARGES</h6>
                                <table class="table table-sm">
                                    <tr><th>Rubrique</th><th class="text-end">Montant (F)</th></tr>
                                    <tr><td>Achats consommés</td><td class="text-end text-danger"><?= number_format($achats, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Charges de personnel</td><td class="text-end text-danger"><?= number_format($salaires, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Services externes</td><td class="text-end text-danger"><?= number_format($services_externes, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Dotations aux amortissements</td><td class="text-end text-danger"><?= number_format($dotations, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Provisions</td><td class="text-end text-danger"><?= number_format($provisions, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Impôts sur résultats</td><td class="text-end text-danger"><?= number_format($impots, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Charges financières</td><td class="text-end text-danger"><?= number_format($charges_financieres, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="table-danger fw-bold"><td>TOTAL CHARGES</td><td class="text-end"><?= number_format($total_charges_cg, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                        <div class="alert alert-success text-center">
                            <h4>RÉSULTAT NET CG : <?= number_format($resultat_cg, 0, ',', ' ') ?> FCFA</h4>
                        </div>
                    </div>
                </div>

                <!-- Partie 2: Charges non incorporables -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">2. CHARGES NON INCORPORABLES (à ajouter)</div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr><th>Nature</th><th>Justification</th><th class="text-end">Montant (F)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Dotations excédentaires (dérogatoires)</td>
                                    <td>Fraction des dotations > dotations fiscales</td>
                                    <td class="text-end text-danger"><?= number_format($charges_non_incorporables['dotations_excedentaires'], 0, ',', ' ') ?> F</td>
                                </tr>
                                <tr><td>Provisions pour litiges</td>
                                    <td>Couverture d'un risque non certain</td>
                                    <td class="text-end text-danger"><?= number_format($charges_non_incorporables['provisions'], 0, ',', ' ') ?> F</td>
                                </tr>
                                <tr><td>Impôts sur les résultats</td>
                                    <td>Charges non liées à l'exploitation</td>
                                    <td class="text-end text-danger"><?= number_format($charges_non_incorporables['impots_resultats'], 0, ',', ' ') ?> F</td>
                                </tr>
                                <tr class="table-warning fw-bold"><td colspan="2">TOTAL CHARGES NON INCORPORABLES</td>
                                    <td class="text-end"><?= number_format($total_non_incorporables, 0, ',', ' ') ?> F</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="alert alert-secondary mt-2">
                            <strong>💡 Explication :</strong> Ces charges sont exclues de la CAE car elles ne correspondent pas à l'exploitation normale.
                        </div>
                    </div>
                </div>

                <!-- Partie 3: Charges supplétives -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">3. CHARGES SUPPLÉTIVES (à retrancher)</div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr><th>Nature</th><th>Justification</th><th class="text-end">Montant (F)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Rémunération du dirigeant</td>
                                    <td>Travail non rémunéré dans la CG</td>
                                    <td class="text-end text-danger"><?= number_format($charges_suppletives['remuneration_dirigeant'], 0, ',', ' ') ?> F</td>
                                </tr>
                                <tr><td>Rémunération du capital</td>
                                    <td>Intérêt sur capitaux propres (taux 8%)</td>
                                    <td class="text-end text-danger"><?= number_format($charges_suppletives['remuneration_capital'], 0, ',', ' ') ?> F</td>
                                </tr>
                                <tr class="table-info fw-bold"><td colspan="2">TOTAL CHARGES SUPPLÉTIVES</td>
                                    <td class="text-end"><?= number_format($total_suppletives, 0, ',', ' ') ?> F</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="alert alert-secondary mt-2">
                            <strong>💡 Explication :</strong> Ces charges ne figurent pas en CG mais sont prises en CAE pour refléter le coût réel.
                        </div>
                    </div>
                </div>

                <!-- Partie 4: Résultat CAE -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">4. RÉSULTAT DE LA COMPTABILITÉ ANALYTIQUE (CAE)</div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="card bg-secondary text-white">
                                    <div class="card-body">
                                        <h6>Résultat CG</h6>
                                        <h5><?= number_format($resultat_cg, 0, ',', ' ') ?> F</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body">
                                        <h6>+ Charges non incorporables</h6>
                                        <h5>+ <?= number_format($total_non_incorporables, 0, ',', ' ') ?> F</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6>- Charges supplétives</h6>
                                        <h5>- <?= number_format($total_suppletives, 0, ',', ' ') ?> F</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-success text-center mt-3">
                            <h3>RÉSULTAT CAE = <?= number_format($resultat_cg, 0, ',', ' ') ?> + <?= number_format($total_non_incorporables, 0, ',', ' ') ?> - <?= number_format($total_suppletives, 0, ',', ' ') ?> = 
                            <strong class="text-primary"><?= number_format($resultat_cae, 0, ',', ' ') ?> FCFA</strong></h3>
                        </div>
                    </div>
                </div>

                <!-- Partie 5: CMUP - Valorisation des stocks -->
                <div class="card">
                    <div class="card-header bg-dark text-white">5. VALORISATION DES STOCKS - CMUP</div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>📊 Formule CMUP :</strong><br>
                            CMUP = (Stock initial en valeur + Cumul des entrées en valeur) / (Stock initial en quantité + Cumul des entrées en quantité)
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Article</th><th>Stock initial</th><th>Entrées</th><th>Sorties</th><th>CMUP</th><th>Stock final valeur</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cmup as $c): 
                                        $article = $pdo->prepare("SELECT code_article, libelle, stock_initial FROM ARTICLES_STOCK WHERE id = ?");
                                        $article->execute([$c['article_id']]);
                                        $a = $article->fetch();
                                    ?>
                                    <tr>
                                        <td><?= $a['code_article'] ?> - <?= $a['libelle'] ?> </td>
                                        <td class="text-end"><?= number_format($a['stock_initial'], 0, ',', ' ') ?><?= $a['unite'] ?> </td>
                                        <td class="text-end"><?= number_format(200, 0, ',', ' ') ?><?= $a['unite'] ?> </td>
                                        <td class="text-end"><?= number_format(180, 0, ',', ' ') ?><?= $a['unite'] ?> </td>
                                        <td class="text-end fw-bold text-primary"><?= number_format($c['cmup'], 0, ',', ' ') ?> F<?= $a['unite'] ?> </td>
                                        <td class="text-end"><?= number_format(($a['stock_initial'] + 200 - 180) * $c['cmup'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
