<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Formation – Prix de cession interne (PCI) & arbitrage";
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
        .scenario-card { transition: 0.2s; border-left: 5px solid; margin-bottom: 20px; }
        .scenario-capacite { border-left-color: #28a745; }
        .scenario-saturation { border-left-color: #dc3545; }
        .formule { background: #f8f9fa; padding: 10px; border-radius: 8px; font-family: monospace; text-align: center; }
        .alert-arbitrage { background: #e8f4fd; border-left: 5px solid #0d6efd; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-arrow-left-right"></i> Formation – Prix de cession interne (PCI) & arbitrage</h2>
                    <p>Théorie des prix de transfert, capacité inemployée vs saturation, arbitrage DG, règles de fixation</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : CONFLIT INITIAL ==================== -->
                    <h4 class="section-title"><i class="bi bi-question-circle"></i> 1. Le conflit : prix du marché vs intérêt global</h4>
                    <div class="alert alert-warning">
                        <strong>📌 Cas :</strong> Division A produit un composant spécifique (pas de marché externe). Division B fabrique un produit fini vendu 800 €.<br>
                        <strong>Données :</strong> Coûts variables de B = 350 €. Capacité de A inemployée.<br>
                        <strong>Conflit :</strong> A veut appliquer un prix de marché fictif (500 €). B ne peut accepter > 450 €.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card scenario-card scenario-capacite">
                                <div class="card-body">
                                    <h5>🔹 Division A (fournisseur)</h5>
                                    <p>Coût variable unitaire = 250 € (hypothèse)<br>
                                    Capacité inemployée → peut produire sans sacrifier des ventes externes<br>
                                    <strong>Prix souhaité :</strong> 500 € (référence marché fictif)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card scenario-card scenario-capacite">
                                <div class="card-body">
                                    <h5>🔹 Division B (client)</h5>
                                    <p>Prix de vente final = 800 €<br>
                                    Coûts variables hors composant = 350 €<br>
                                    Marge disponible = 800 - 350 = <strong class="text-success">450 €</strong><br>
                                    <strong>Prix max acceptable :</strong> 450 €</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-2">
                        <i class="bi bi-info-circle"></i> <strong>Question :</strong> Le prix du marché (500 €) assure-t-il la convergence des intérêts ? <strong class="text-danger">Non</strong> – car il n'existe pas de marché externe. B perdrait 50 € par unité.
                    </div>

                    <!-- ==================== SECTION 2 : SCÉNARIOS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-diagram-3"></i> 2. Les deux scénarios d'arbitrage</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card scenario-card scenario-capacite h-100">
                                <div class="card-header bg-success text-white">🟢 Scénario A : Capacité inemployée</div>
                                <div class="card-body">
                                    <p><strong>Règle :</strong> PCI = Coût marginal (coût variable + frais spécifiques)</p>
                                    <div class="formule">PCI_min = Coût variable de A (250 €)</div>
                                    <p><strong>Zone acceptable :</strong> [250 € ; 450 €]</p>
                                    <p class="mt-2"><strong>Exemple :</strong> PCI fixé à 400 €</p>
                                    <ul>
                                        <li>Marge A = 400 - 250 = 150 €/u</li>
                                        <li>Marge B = 800 - 350 - 400 = 50 €/u</li>
                                        <li><strong>Marge groupe = 200 €/u → OK</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card scenario-card scenario-saturation h-100">
                                <div class="card-header bg-danger text-white">🔴 Scénario B : Capacité saturée</div>
                                <div class="card-body">
                                    <p><strong>Règle :</strong> PCI = Coût d'opportunité</p>
                                    <div class="formule">PCI_min = Coût variable + Marge externe sacrifiée</div>
                                    <p>Si vente externe = 500 € et Cv = 250 € → Marge externe = 250 €<br>
                                    <strong>PCI_min = 250 + 250 = 500 €</strong></p>
                                    <p><strong>Capacité de paiement de B :</strong> 450 € → <span class="text-danger">Conflit irréductible !</span></p>
                                    <p>L'arbitrage DG dépend de la marge groupe comparative.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 3 : SIMULATEUR ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-sliders"></i> 3. Simulateur – Arbitrage prix de cession</h4>
                    <div class="card bg-light p-3">
                        <form method="post" class="row g-3">
                            <div class="col-md-3">
                                <label>Coût variable A (€)</label>
                                <input type="number" name="cv_a" class="form-control" value="250" required>
                            </div>
                            <div class="col-md-3">
                                <label>Prix vente externe A (€)</label>
                                <input type="number" name="prix_ext_a" class="form-control" value="500" required>
                            </div>
                            <div class="col-md-3">
                                <label>Coûts variables B (hors composant)</label>
                                <input type="number" name="cv_b" class="form-control" value="350" required>
                            </div>
                            <div class="col-md-3">
                                <label>Prix vente final B (€)</label>
                                <input type="number" name="prix_b" class="form-control" value="800" required>
                            </div>
                            <div class="col-md-4">
                                <label>PCI proposé (€)</label>
                                <input type="number" name="pci" class="form-control" value="400" required>
                            </div>
                            <div class="col-md-4">
                                <label>Quantité (unités)</label>
                                <input type="number" name="quantite" class="form-control" value="500" required>
                            </div>
                            <div class="col-md-4">
                                <label>Saturation ?</label>
                                <select name="saturation" class="form-control">
                                    <option value="0">Capacité inemployée</option>
                                    <option value="1">Capacité saturée (coût d'opportunité)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="simuler" class="btn btn-primary">Simuler l'arbitrage</button>
                            </div>
                        </form>

                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simuler'])) {
                            $cv_a = (float)$_POST['cv_a'];
                            $prix_ext_a = (float)$_POST['prix_ext_a'];
                            $cv_b = (float)$_POST['cv_b'];
                            $prix_b = (float)$_POST['prix_b'];
                            $pci = (float)$_POST['pci'];
                            $q = (int)$_POST['quantite'];
                            $saturation = (int)$_POST['saturation'];
                            
                            $marge_dispo_b = $prix_b - $cv_b;
                            $prix_max_b = $marge_dispo_b;
                            
                            if ($saturation == 1) {
                                $marge_externe_a = $prix_ext_a - $cv_a;
                                $prix_min_a = $prix_ext_a; // coût d'opportunité
                            } else {
                                $marge_externe_a = 0;
                                $prix_min_a = $cv_a;
                            }
                            
                            $marge_a = ($pci - $cv_a) * $q;
                            $marge_b = ($prix_b - $cv_b - $pci) * $q;
                            $marge_groupe = ($prix_b - $cv_b - $cv_a) * $q;
                            if ($saturation == 1) {
                                $marge_groupe_alternative = ($prix_ext_a - $cv_a) * $q;
                            } else {
                                $marge_groupe_alternative = 0;
                            }
                            
                            echo <<<HTML
                            <div class="alert alert-info mt-4">
                                <strong>📊 Résultats de la simulation :</strong>
                                <div class="table-responsive mt-2">
                                    <table class="table table-bordered">
                                        <thead class="table-dark"><tr><th>Indicateur</th><th>Valeur</th></tr></thead>
                                        <tbody>
                                            <tr><td>Marge disponible pour B (max PCI)</td><td class="fw-bold">{$prix_max_b} €</td></tr>
                                            <tr><td>Prix minimum acceptable pour A</td><td class="fw-bold">{$prix_min_a} €</td></tr>
                                            <tr><td>PCI proposé</td><td class="fw-bold">{$pci} €</td></tr>
                                            <tr><td>Marge de A</td><td class="fw-bold " . ($marge_a >= 0 ? 'text-success' : 'text-danger') . ">{$marge_a} €</td></tr>
                                            <tr><td>Marge de B</td><td class="fw-bold " . ($marge_b >= 0 ? 'text-success' : 'text-danger') . ">{$marge_b} €</td></tr>
                                            <tr><td>Marge groupe (avec transfert)</td><td class="fw-bold text-success">{$marge_groupe} €</td></tr>
                        HTML;
                            if ($saturation == 1) {
                                echo "<tr><td>Marge groupe alternative (vente externe A)</td><td class='fw-bold text-warning'>{$marge_groupe_alternative} €</td></tr>";
                                if ($marge_groupe > $marge_groupe_alternative) {
                                    echo "<tr><td colspan='2' class='text-success'>✅ Décision DG : Imposer le transfert (rentabilité groupe supérieure)</td></tr>";
                                } else {
                                    echo "<tr><td colspan='2' class='text-danger'>❌ Décision DG : Refuser le transfert (vente externe plus rentable)</td></tr>";
                                }
                            }
                            echo "</tbody></table></div></div>";
                        }
                        ?>
                    </div>

                    <!-- ==================== SECTION 4 : NOTE DE CADRAGE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-file-text"></i> 4. Note de cadrage – Politique de Prix de Cession Interne (PCI)</h4>
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>📋 Principes directeurs</h5>
                            <p>La règle fondamentale est la <strong>maximisation de la marge contributive totale de l'entreprise</strong>.</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr><th>Scénario</th><th>Situation</th><th>Règle de prix de cession</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td class="text-success">Scénario A</td><td>Capacité de production inemployée</td><td>Coût marginal (coûts variables + frais spécifiques)</td></tr>
                                        <tr><td class="text-danger">Scénario B</td><td>Capacité de production saturée</td><td>Coût d'opportunité (Cv + marge externe sacrifiée)</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <h5 class="mt-3">⚖️ Mécanisme de résolution des conflits</h5>
                            <ul>
                                <li>En cas de désaccord, présentation du calcul de rentabilité globale à la DG</li>
                                <li>Neutralité divisionnelle : système de double comptabilisation ou subvention interne si transfert imposé</li>
                                <li>Le prix du marché ne doit pas être appliqué systématiquement lorsqu'il est fictif ou indisponible</li>
                            </ul>
                        </div>
                    </div>

                    <!-- ==================== SECTION 5 : SYNTHÈSE ==================== -->
                    <div class="alert alert-success mt-4">
                        <i class="bi bi-check-circle-fill"></i> <strong>Synthèse pédagogique :</strong><br>
                        • En capacité <strong>inemployée</strong> → le PCI doit couvrir le <strong>coût marginal</strong>. L'opération est créatrice de valeur.<br>
                        • En capacité <strong>saturée</strong> → le PCI doit intégrer le <strong>coût d'opportunité</strong>. Seule la rentabilité globale départage.<br>
                        • Le rôle du DG est d'arbitrer pour <strong>maximiser la marge consolidée</strong>, pas pour satisfaire une division individuelle.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
