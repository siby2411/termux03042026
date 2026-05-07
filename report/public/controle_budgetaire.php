<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Contrôle Budgétaire - Analyse des Écarts";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice_selected = $_GET['exercice'] ?? date('Y');

// Traitement nouvelle saisie budget
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'set_budget') {
        $exercice = (int)$_POST['exercice'];
        $mois = (int)$_POST['mois'];
        $type = $_POST['type_budget'];
        $montant = (float)$_POST['montant'];
        
        $stmt = $pdo->prepare("INSERT INTO BUDGETS_PREVISIONNELS (exercice, mois, libelle, type_budget, montant_prevu) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE montant_prevu = ?");
        $stmt->execute([$exercice, $mois, "Budget $type", $type, $montant, $montant]);
        $message = "✅ Budget enregistré avec succès";
    }
}

// Récupération des budgets
$budgets = $pdo->prepare("
    SELECT * FROM BUDGETS_PREVISIONNELS 
    WHERE exercice = ? 
    ORDER BY mois, type_budget
");
$budgets->execute([$exercice_selected]);
$budgets_data = $budgets->fetchAll();

// Récupération des écarts
$ecarts = $pdo->prepare("
    SELECT * FROM ECARTS_GESTION 
    WHERE exercice = ? 
    ORDER BY mois, type_ecart DESC
");
$ecarts->execute([$exercice_selected]);
$ecarts_data = $ecarts->fetchAll();

// Agrégation par type de budget
$resume_budgets = [];
foreach($budgets_data as $b) {
    $mois_nom = date('F', mktime(0,0,0,$b['mois'],1));
    $type = $b['type_budget'];
    if(!isset($resume_budgets[$type]['prevu'])) $resume_budgets[$type]['prevu'] = 0;
    if(!isset($resume_budgets[$type]['reel'])) $resume_budgets[$type]['reel'] = 0;
    $resume_budgets[$type]['prevu'] += $b['montant_prevu'];
    $resume_budgets[$type]['reel'] += $b['montant_reel'];
    $resume_budgets[$type]['libelle'] = $b['libelle'];
}

// Calcul des totaux
$total_prevu = array_sum(array_column($resume_budgets, 'prevu'));
$total_reel = array_sum(array_column($resume_budgets, 'reel'));
$total_ecart = $total_reel - $total_prevu;
?>

<div class="row">
    <div class="col-md-12">
        <!-- En-tête cas pratique -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-building"></i> CAS PRATIQUE : GHI SARL - Fabrication de meubles</h5>
                <small>Analyse budgétaire et contrôle de gestion - Exercice <?= $exercice_selected ?></small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <strong>📊 Contexte :</strong> GHI SARL est une entreprise de fabrication de meubles.
                            Le contrôle budgétaire permet d'analyser les écarts entre le prévisionnel et le réalisé.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <strong>🎯 Objectif :</strong> Identifier les causes des écarts et proposer des actions correctives.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sélection exercice -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label>Sélectionner l'exercice</label>
                        <select name="exercice" class="form-select" onchange="this.form.submit()">
                            <option value="2025" <?= $exercice_selected == 2025 ? 'selected' : '' ?>>2025</option>
                            <option value="2026" <?= $exercice_selected == 2026 ? 'selected' : '' ?>>2026</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Synthèse budgétaire -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-pie-chart"></i> Synthèse Budgétaire - Premier Semestre <?= $exercice_selected ?></h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Budget Prévisionnel</h6>
                                <h3 class="text-primary"><?= number_format($total_prevu, 0, ',', ' ') ?> F</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Réalisé</h6>
                                <h3 class="text-info"><?= number_format($total_reel, 0, ',', ' ') ?> F</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Écart Global</h6>
                                <h3 class="<?= $total_ecart >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($total_ecart), 0, ',', ' ') ?> F
                                    <?= $total_ecart >= 0 ? '(▲)' : '(▼)' ?>
                                </h3>
                                <small>Taux de réalisation : <?= $total_prevu > 0 ? number_format(($total_reel/$total_prevu)*100, 1) : 0 ?>%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau détaillé des budgets -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-table"></i> Analyse détaillée par poste budgétaire</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Poste budgétaire</th>
                                <th>Prévisionnel (FCFA)</th>
                                <th>Réalisé (FCFA)</th>
                                <th>Écart (FCFA)</th>
                                <th>Taux réalisation</th>
                                <th>Analyse</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($resume_budgets as $type => $data): 
                                $ecart = $data['reel'] - $data['prevu'];
                                $taux = $data['prevu'] > 0 ? ($data['reel'] / $data['prevu']) * 100 : 0;
                                $est_positif = ($type == 'VENTES' && $ecart > 0) || ($type != 'VENTES' && $ecart < 0);
                            ?>
                            <tr>
                                <td>
                                    <strong><?= $type ?></strong><br>
                                    <small><?= htmlspecialchars($data['libelle'] ?? '') ?></small>
                                </td>
                                <td class="text-end"><?= number_format($data['prevu'], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($data['reel'], 0, ',', ' ') ?> F</td>
                                <td class="text-end <?= $est_positif ? 'text-success' : 'text-danger' ?>">
                                    <?= ($ecart >= 0 ? '+' : '') . number_format($ecart, 0, ',', ' ') ?> F
                                </td>
                                <td class="text-center">
                                    <div class="progress">
                                        <div class="progress-bar <?= $taux >= 100 ? 'bg-success' : ($taux >= 80 ? 'bg-warning' : 'bg-danger') ?>" style="width: <?= min(100, $taux) ?>%">
                                            <?= number_format($taux, 1) ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($type == 'VENTES' && $ecart < 0): ?>
                                        ⚠️ Baisse des ventes
                                    <?php elseif($type == 'VENTES' && $ecart > 0): ?>
                                        ✅ Croissance des ventes
                                    <?php elseif($ecart > 0): ?>
                                        ⚠️ Dépassement de budget
                                    <?php elseif($ecart < 0): ?>
                                        ✅ Économie réalisée
                                    <?php else: ?>
                                        ➖ Conforme au budget
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td class="text-end">TOTAL :</td>
                                <td class="text-end"><?= number_format($total_prevu, 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($total_reel, 0, ',', ' ') ?> F</td>
                                <td class="text-end <?= $total_ecart >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($total_ecart), 0, ',', ' ') ?> F
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Analyse des écarts mensuels -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h6><i class="bi bi-graph-down"></i> Évolution des écarts mensuels</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>Mois</th><th>Écart favorable</th><th>Écart défavorable</th></tr>
                            </thead>
                            <tbody>
                                <?php 
                                $ecarts_mensuels = [];
                                foreach($budgets_data as $b) {
                                    $ecart = $b['montant_reel'] - $b['montant_prevu'];
                                    if(!isset($ecarts_mensuels[$b['mois']]['fav'])) $ecarts_mensuels[$b['mois']]['fav'] = 0;
                                    if(!isset($ecarts_mensuels[$b['mois']]['def'])) $ecarts_mensuels[$b['mois']]['def'] = 0;
                                    if($b['type_budget'] == 'VENTES') {
                                        if($ecart > 0) $ecarts_mensuels[$b['mois']]['fav'] += $ecart;
                                        else $ecarts_mensuels[$b['mois']]['def'] += abs($ecart);
                                    } else {
                                        if($ecart < 0) $ecarts_mensuels[$b['mois']]['fav'] += abs($ecart);
                                        else $ecarts_mensuels[$b['mois']]['def'] += $ecart;
                                    }
                                }
                                ksort($ecarts_mensuels);
                                foreach($ecarts_mensuels as $mois => $e): 
                                    $mois_nom = date('F', mktime(0,0,0,$mois,1));
                                ?>
                                <tr>
                                    <td><?= $mois_nom ?></td>
                                    <td class="text-success"><?= number_format($e['fav'], 0, ',', ' ') ?> F</td>
                                    <td class="text-danger"><?= number_format($e['def'], 0, ',', ' ') ?> F</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6><i class="bi bi-lightbulb"></i> Recommandations et actions correctives</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><i class="bi bi-arrow-right-circle text-success"></i> <strong>Écart favorable sur charges :</strong> Poursuivre les efforts de maîtrise</li>
                            <li><i class="bi bi-arrow-right-circle text-warning"></i> <strong>Écart défavorable sur ventes :</strong> Renforcer la force de vente</li>
                            <li><i class="bi bi-arrow-right-circle text-primary"></i> <strong>Objectif semestriel :</strong> Atteindre 95% de réalisation budgétaire</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
