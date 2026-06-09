<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Contrôle de gestion – Structure multidimensionnelle & prix de transfert";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';
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
        .center-card { transition: 0.2s; border-left: 5px solid #0d6efd; margin-bottom: 15px; }
        .center-card:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .table-scenario { font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-diagram-3"></i> Contrôle de gestion – Structure multidimensionnelle & prix de transfert</h2>
                    <p>Centres de responsabilité, prix de cession interne, résolution des conflits (théorie d'Anthony & Dearden)</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : CADRE CONCEPTUEL ==================== -->
                    <h4 class="section-title"><i class="bi bi-lightbulb"></i> 1. Cadre conceptuel – Centres de responsabilité</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card center-card"><div class="card-body"><h5>💰 Centre de coût</h5><p>Responsable des ressources consommées (ex: service maintenance).</p><small>Indicateur : écart sur budget</small></div></div>
                        </div>
                        <div class="col-md-3">
                            <div class="card center-card"><div class="card-body"><h5>📈 Centre de revenu</h5><p>Responsable du chiffre d'affaires (ex: force de vente).</p><small>Indicateur : CA / objectif</small></div></div>
                        </div>
                        <div class="col-md-3">
                            <div class="card center-card"><div class="card-body"><h5>🏆 Centre de profit</h5><p>Responsable du résultat (marge commerciale).</p><small>Indicateur : marge nette</small></div></div>
                        </div>
                        <div class="col-md-3">
                            <div class="card center-card"><div class="card-body"><h5>📊 Centre d'investissement</h5><p>Responsable de la rentabilité des capitaux investis.</p><small>Indicateur : ROI / EVA</small></div></div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 2 : SAISIE DES CENTRES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-building"></i> 2. Centres de responsabilité enregistrés</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr><th>Code</th><th>Nom</th><th>Type</th><th>Niveau</th><th>Parent</th><th>Actif</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                $centres = $pdo->query("SELECT c.*, p.nom as parent_nom FROM centres_responsabilite c LEFT JOIN centres_responsabilite p ON c.parent_id = p.id ORDER BY c.niveau_hierarchique, c.code")->fetchAll();
                                if(empty($centres)) {
                                    echo '<tr><td colspan="6" class="text-center">Aucun centre. <button class="btn btn-sm btn-primary" onclick="ajouterExemples()">Ajouter les centres de démonstration</button></td></tr>';
                                } else {
                                    foreach($centres as $c) {
                                        $type_badge = $c['type'] == 'Coût' ? 'secondary' : ($c['type'] == 'Revenu' ? 'info' : ($c['type'] == 'Profit' ? 'success' : 'primary'));
                                        echo "<tr>";
                                        echo "<td>{$c['code']}</td>";
                                        echo "<td>{$c['nom']}</td>";
                                        echo "<td><span class='badge bg-{$type_badge}'>{$c['type']}</span></td>";
                                        echo "<td>{$c['niveau_hierarchique']}</td>";
                                        echo "<td>{$c['parent_nom']}?</div></div></div></td>";
                                        echo "<td>" . ($c['actif'] ? '✅' : '❌') . "</td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 3 : CAS PRATIQUE (CONFLIT) ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 3. Cas pratique : Conflit UC vs UA (Usine Composants / Unité Assemblage)</h4>
                    <div class="alert alert-info">
                        <strong>📌 Données du marché :</strong><br>
                        Coût variable UC : 80 € | Prix de vente externe UC : 120 € | Capacité UC : 10 000 u<br>
                        Coût transformation UA (hors composant) : 100 € | Prix vente tablette UA : 250 €
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header fw-bold">Scénario 1 : Refus de la commande interne</div>
                                <div class="card-body">
                                    <p>UA demande 2 000 unités à 90 €. UC refuse car il perd 30 €/u par rapport à la vente externe (120 €).</p>
                                    <p class="text-danger"><strong>Perte de marge globale pour l'entreprise :</strong> 2 000 × 70 € = 140 000 € de marge non réalisée.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header fw-bold">Scénario 2 : Arbitrage central (solution optimale)</div>
                                <div class="card-body">
                                    <p>Le siège impose le transfert au coût variable (80 €) et compense UC pour la perte de marge externe.</p>
                                    <p class="text-success"><strong>Marge globale :</strong> 2 000 × (250 - 80 - 100) = 140 000 €.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 4 : SIMULATEUR PRIX DE TRANSFERT ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-sliders"></i> 4. Simulateur – Prix de transfert & double tarification</h4>
                    <form method="post" class="card p-3 mb-3">
                        <div class="row g-3">
                            <div class="col-md-3"><label>Quantité demandée par UA</label><input type="number" name="quantite" class="form-control" value="2000" required></div>
                            <div class="col-md-3"><label>Coût variable UC (€)</label><input type="number" name="cout_variable" class="form-control" value="80" required></div>
                            <div class="col-md-3"><label>Prix de marché externe (€)</label><input type="number" name="prix_marche" class="form-control" value="120" required></div>
                            <div class="col-md-3"><label>Prix proposé par UA (€)</label><input type="number" name="prix_propose" class="form-control" value="90" required></div>
                            <div class="col-md-12"><button type="submit" name="simuler" class="btn btn-primary">Simuler les impacts</button></div>
                        </div>
                    </form>

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simuler'])) {
                        $q = (float)$_POST['quantite'];
                        $cv = (float)$_POST['cout_variable'];
                        $pm = (float)$_POST['prix_marche'];
                        $pp = (float)$_POST['prix_propose'];
                        
                        $marge_uc = $pm - $cv;
                        $marge_ua_externe = 250 - $pm - 100;
                        $marge_ua_interne = 250 - $pp - 100;
                        $marge_globale = 250 - $cv - 100;
                        
                        $gain_uc = $q * $pp - $q * $cv;
                        $gain_ua = $q * 250 - $q * $pp - $q * 100;
                        
                        echo <<<HTML
                        <div class="alert alert-info mt-3">
                            <strong>Résultats de la simulation :</strong><br>
                            <table class="table table-sm table-bordered mt-2">
                                <thead class="table-dark"><tr><th>Indicateur</th><th>Valeur</th></tr></thead>
                                <tbody>
                                    <tr><td>Marge unitaire UC (vente externe)</td><td>{$marge_uc} €</td></tr>
                                    <tr><td>Marge unitaire UA (achat externe)</td><td>" . round($marge_ua_externe, 2) . " €</td></tr>
                                    <tr><td>Marge unitaire UA (achat interne à {$pp} €)</td><td>" . round($marge_ua_interne, 2) . " €</td></tr>
                                    <tr><td class="fw-bold">Marge globale pour l'entreprise</td><td class="fw-bold text-success">" . round($marge_globale, 2) . " €/u → " . round($marge_globale * $q, 0) . " €</td></tr>
                                </tbody>
                            </table>
                            <p class="mt-2"><strong>💰 Double tarification :</strong> Créditer UC à {$pm} €, débiter UA à {$cv} €. Subvention siège = " . round(($pm - $cv) * $q, 0) . " €.</p>
                            <p><strong>⚖️ Arbitrage central :</strong> Le siège impose la vente à {$cv} € et compense UC pour la perte de marge externe.</p>
                        </div>
HTML;
                    }
                    ?>

                    <!-- ==================== SECTION 5 : KPI & TABLEAU DE BORD ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-graph-up"></i> 5. Indicateurs de performance (KPIs) – Prix de transfert</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-center bg-light">
                                <div class="card-body"><h6>📊 Taux de service interne</h6><h3>85%</h3><small>Volume interne / Volume total</small></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center bg-light">
                                <div class="card-body"><h6>📈 Écart de compétitivité interne</h6><h3 class="text-warning">+12%</h3><small>Prix interne vs marché externe</small></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center bg-light">
                                <div class="card-body"><h6>🏆 Conflits résolus / trimestre</h6><h3>3</h3><small>Arbitrages par siège</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Rappel d'Anthony & Dearden (paradoxe de la décentralisation) :</strong> La recherche d'une compétition interne, si elle est mal orchestrée, génère des conflits de sous-optimisation. La solution réside dans l'arbitrage centralisé et la double tarification.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function ajouterExemples() {
    alert("Fonction d'ajout des centres de démonstration (UC, UA, Siège) – à implémenter via script SQL.");
}
</script>
<?php include 'inc_footer.php'; ?>
