<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Performance des centres – Analyse des écarts & centres de profit";
include 'inc_navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .product-card { transition: 0.2s; border-left: 5px solid; margin-bottom: 15px; }
        .product-favorable { border-left-color: #28a745; background-color: #e8f8f0; }
        .product-defavorable { border-left-color: #dc3545; background-color: #fee; }
        .product-neutre { border-left-color: #ffc107; background-color: #fffae6; }
        .table-comparatif { font-size: 0.9rem; }
        .simulation-card { background: #f8f9fa; border-radius: 12px; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-diagram-3"></i> Performance des centres – Analyse des écarts & centres de profit</h2>
                    <p>Cas pratique "Le Brack" : contradiction volume/rentabilité, analyse d'écart sur marge, prix de cession interne (PCI), centres de profit</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : DONNÉES INITIALES ==================== -->
                    <h4 class="section-title"><i class="bi bi-table"></i> 1. Données de base – Produits "Le Brack"</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-comparatif">
                            <thead class="table-dark">
                                <tr><th>Produit</th><th>Quantité prévue</th><th>Quantité réelle</th><th>Écart volume</th><th>CA prévu (K FCFA)</th><th>CA réel (K FCFA)</th><th>Coût unitaire</th><th>Prix vente</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="fw-bold">Fauteuil</td><td class="text-end">18 000</td><td class="text-end">13 500</td><td class="text-danger">-4 500</td><td class="text-end">27 000</td><td class="text-end">20 250</td><td class="text-end">600</td><td class="text-end">1 500</td></tr>
                                <tr><td class="fw-bold">Table</td><td class="text-end">9 000</td><td class="text-end">7 500</td><td class="text-danger">-1 500</td><td class="text-end">28 800</td><td class="text-end">24 000</td><td class="text-end">1 920</td><td class="text-end">3 200</td></tr>
                                <tr><td class="fw-bold">Armoire</td><td class="text-end">3 000</td><td class="text-end">10 500</td><td class="text-success">+7 500</td><td class="text-end">16 500</td><td class="text-end">57 750</td><td class="text-end">4 675</td><td class="text-end">5 500</td></tr>
                                <tr><td class="fw-bold">TOTAL</td><td class="text-end"><strong>30 000</strong></td><td class="text-end"><strong>31 500</strong></td><td class="text-success"><strong>+1 500</strong></td><td class="text-end"><strong>72 300</strong></td><td class="text-end"><strong>102 000</strong></td><td class="text-end">-</td><td class="text-end">-</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 2 : ANALYSE DES ÉCARTS SUR MARGE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 2. Analyse des écarts sur marge</h4>
                    <?php
                    // Données
                    $produits = [
                        'Fauteuil' => ['qty_prev' => 18000, 'qty_real' => 13500, 'marge_unit' => 900, 'prix' => 1500, 'cout' => 600],
                        'Table' => ['qty_prev' => 9000, 'qty_real' => 7500, 'marge_unit' => 1280, 'prix' => 3200, 'cout' => 1920],
                        'Armoire' => ['qty_prev' => 3000, 'qty_real' => 10500, 'marge_unit' => 825, 'prix' => 5500, 'cout' => 4675]
                    ];
                    
                    $marge_totale_prev = 0;
                    $marge_totale_real = 0;
                    foreach ($produits as $p) {
                        $marge_totale_prev += $p['qty_prev'] * $p['marge_unit'];
                        $marge_totale_real += $p['qty_real'] * $p['marge_unit'];
                    }
                    $ecart_marge = $marge_totale_real - $marge_totale_prev;
                    ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-center bg-light">
                                <div class="card-body">
                                    <h5>📊 Marge prévue</h5>
                                    <h3><?= number_format($marge_totale_prev, 0, ',', ' ') ?> K FCFA</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center bg-light">
                                <div class="card-body">
                                    <h5>📈 Marge réelle</h5>
                                    <h3><?= number_format($marge_totale_real, 0, ',', ' ') ?> K FCFA</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center <?= $ecart_marge >= 0 ? 'bg-success' : 'bg-danger' ?> text-white">
                                <div class="card-body">
                                    <h5>📉 Écart sur marge</h5>
                                    <h3><?= number_format($ecart_marge, 0, ',', ' ') ?> K FCFA</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <strong>🔍 Contradiction :</strong> Le volume total a augmenté (+1 500 unités) et le CA a progressé (+29 700 K FCFA), mais la marge globale a diminué ! La structure des ventes s'est dégradée au profit des armoires (produit à faible marge unitaire).
                    </div>

                    <!-- ==================== SECTION 3 : PRIX DE CESSION INTERNE (PCI) ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-arrow-left-right"></i> 3. Prix de Cession Interne (PCI) – Centres de profit</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Produit</th><th>Coût standard (Usine)</th><th>PCI (Coût + marge 20%)</th><th>Prix de vente final</th><th>Marge magasin (par unité)</th><th>Rentabilité</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="fw-bold">Fauteuil</td><td class="text-end">600</td><td class="text-end">720</td><td class="text-end">1 500</td><td class="text-end text-success">780</td><td><span class="badge bg-success">Rentable</span></td></tr>
                                <tr><td class="fw-bold">Table</td><td class="text-end">1 920</td><td class="text-end">2 304</td><td class="text-end">3 200</td><td class="text-end text-success">896</td><td><span class="badge bg-success">Rentable</span></td></tr>
                                <tr><td class="fw-bold">Armoire</td><td class="text-end">4 675</td><td class="text-end">5 610</td><td class="text-end">5 500</td><td class="text-end text-danger">-110</td><td><span class="badge bg-danger">Destructeur de valeur</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info">
                        <strong>📌 Prix de Cession Interne (PCI) :</strong> Outil de pilotage permettant de transformer chaque unité de distribution en centre de profit. Le PCI aligne les intérêts des vendeurs avec la rentabilité globale de l'entreprise.
                    </div>

                    <!-- ==================== SECTION 4 : SIMULATION IMPACT MAGASIN ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-sliders"></i> 4. Simulation d'impact – Magasin de Dakar</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="simulation-card">
                                <h5>🏢 Ancienne organisation (centre de CA)</h5>
                                <p><strong>Objectif :</strong> Maximiser le chiffre d'affaires</p>
                                <p><strong>Comportement :</strong> Pousse les armoires (produit cher)</p>
                                <p><strong>Résultat sur armoires :</strong> 5 000 unités × (-110) = <span class="text-danger fw-bold">-550 000 FCFA</span></p>
                                <p class="text-muted">Le responsable pense bien faire car son CA augmente, mais il détruit de la valeur.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="simulation-card">
                                <h5>🏢 Nouvelle organisation (centre de profit)</h5>
                                <p><strong>Objectif :</strong> Maximiser la marge nette</p>
                                <p><strong>Comportement :</strong> Pousse les fauteuils et tables (forte marge)</p>
                                <p><strong>Calcul :</strong><br>
                                3 000 fauteuils × 780 = +2 340 000<br>
                                2 000 tables × 896 = +1 792 000<br>
                                5 000 armoires × (-110) = -550 000<br>
                                <span class="text-success fw-bold">Total = +3 582 000 FCFA</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 5 : TABLEAU COMPARATIF ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-bar-chart"></i> 5. Comparaison des deux organisations</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Indicateur</th><th>Ancienne organisation (CA)</th><th>Nouvelle organisation (Marge nette)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Objectif</td><td>Maximiser le chiffre d'affaires</td><td>Maximiser la marge nette</td></tr>
                                <tr><td>Comportement induit</td><td>Pousse les armoires (produit cher)</td><td>Pousse les fauteuils/tables (forte marge)</td></tr>
                                <tr><td>Résultat du magasin</td><td class="text-danger">-550 000 FCFA</td><td class="text-success">+3 582 000 FCFA</td></tr>
                                <tr><td>Alignement avec stratégie groupe</td><td class="text-danger">❌ Non</td><td class="text-success">✅ Oui</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 6 : TABLEAU DE BORD ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-speedometer2"></i> 6. Tableau de bord – Performance des centres</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card product-favorable">
                                <div class="card-body">
                                    <h5>✅ Fauteuil</h5>
                                    <p>Marge unitaire : 780 FCFA<br>Rentabilité : Élevée<br>Priorité commerciale : Haute</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card product-favorable">
                                <div class="card-body">
                                    <h5>✅ Table</h5>
                                    <p>Marge unitaire : 896 FCFA<br>Rentabilité : Élevée<br>Priorité commerciale : Haute</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card product-defavorable">
                                <div class="card-body">
                                    <h5>❌ Armoire</h5>
                                    <p>Marge unitaire : -110 FCFA<br>Rentabilité : Négative<br>Priorité commerciale : Réduire stock</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 7 : RECOMMANDATIONS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-check-circle"></i> 7. Recommandations stratégiques</h4>
                    <div class="alert alert-success">
                        <strong>💡 Recommandations pour la direction :</strong>
                        <ul class="mt-2">
                            <li>Transformer chaque unité de distribution en <strong>centre de profit</strong> (évaluation sur marge, pas sur CA)</li>
                            <li>Mettre en place des <strong>Prix de Cession Interne (PCI)</strong> basés sur le coût standard + marge usine</li>
                            <li>Réorienter la force de vente vers les <strong>produits à forte contribution</strong> (fauteuils et tables)</li>
                            <li>Réduire les stocks d'armoires (produit destructeur de valeur)</li>
                            <li>Former les responsables de magasin à la lecture des marges et à la gestion de mix-produit</li>
                        </ul>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle"></i> <strong>Synthèse pédagogique :</strong> La performance d'un centre ne se mesure pas à son chiffre d'affaires, mais à sa <strong>contribution à la marge globale</strong>. Le Prix de Cession Interne est l'outil qui aligne les intérêts des unités opérationnelles avec la stratégie financière de l'entreprise.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
