<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Analyse des écarts - Coûts préétablis et marges";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$ecarts = [];

// Récupération des coûts préétablis
$cout_preet = $pdo->query("SELECT * FROM COUTS_PREETABLIS")->fetchAll();

// Calcul des écarts sur charges directes (simulation)
$quantite_reelle = 1000;
$prix_reel = 12;
$prix_standard = 10;
$ecart_prix = ($prix_reel - $prix_standard) * $quantite_reelle;
$ecart_quantite = ($quantite_reelle - 1100) * $prix_standard;
$ecart_global = $ecart_prix + $ecart_quantite;

// Écart sur marge (basé sur CA)
$ca_reel = 1500000;
$ca_budget = 1400000;
$marge_reelle = 300000;
$marge_budget = 280000;
$ecart_ca = $ca_reel - $ca_budget;
$ecart_marge = $marge_reelle - $marge_budget;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Analyse des écarts - Coûts préétablis et marges</h5>
                <small>Détail des écarts sur charges directes, indirectes, et résultats</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary">Écarts sur charges directes</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><td>Écart sur prix</td><td class="text-end"><?= number_format($ecart_prix,0,',',' ') ?> F</td><td><?= $ecart_prix > 0 ? 'Défavorable' : 'Favorable' ?></td></tr>
                                    <tr><td>Écart sur quantité</td><td class="text-end"><?= number_format($ecart_quantite,0,',',' ') ?> F</td><td><?= $ecart_quantite > 0 ? 'Défavorable' : 'Favorable' ?></td></tr>
                                    <tr class="table-primary"><td>Écart global</td><td class="text-end fw-bold"><?= number_format($ecart_global,0,',',' ') ?> F</td><td></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary">Écarts sur marge et chiffre d'affaires</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><td>Écart sur CA</td><td class="text-end"><?= number_format($ecart_ca,0,',',' ') ?> F</td><td><?= $ecart_ca > 0 ? 'Favorable' : 'Défavorable' ?></td></tr>
                                    <tr><td>Écart sur marge</td><td class="text-end"><?= number_format($ecart_marge,0,',',' ') ?> F</td><td><?= $ecart_marge > 0 ? 'Favorable' : 'Défavorable' ?></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary">Coûts préétablis par centre de profit</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-dark"><tr><th>Centre de profit</th><th>Coût unitaire standard</th><th>Coût unitaire réel</th><th>Écart unitaire</th><th>Écart total (pour 1000u)</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($cout_preet as $c): 
                                                $ecart_unit = $c['cout_unitaire_reel'] - $c['cout_unitaire_standard'];
                                                $ecart_total = $ecart_unit * 1000;
                                            ?>
                                            <tr>
                                                <td><?= $c['centre_profit'] ?></td>
                                                <td class="text-end"><?= number_format($c['cout_unitaire_standard'],0,',',' ') ?> F</td>
                                                <td class="text-end"><?= number_format($c['cout_unitaire_reel'],0,',',' ') ?> F</td>
                                                <td class="text-end"><?= number_format($ecart_unit,0,',',' ') ?> F</td>
                                                <td class="text-end"><?= number_format($ecart_total,0,',',' ') ?> F</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-2">
                                    💡 Un écart positif signifie un surcoût (défavorable), négatif = économie (favorable).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
