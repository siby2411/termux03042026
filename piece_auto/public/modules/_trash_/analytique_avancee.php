<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Comptabilité Analytique Avancée - Business Intelligence";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice = $_GET['exercice'] ?? date('Y');
$section_id = $_GET['section'] ?? 1;

// Récupération des sections analytiques
$sections = $pdo->query("SELECT * FROM SECTIONS_ANALYTIQUES ORDER BY type_section, code")->fetchAll();

// Calcul des KPI par section
$kpi_sections = [];
foreach($sections as $s) {
    // Calcul du CA par section
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(montant), 0) as total
        FROM ECRITURES_COMPTABLES 
        WHERE section_analytique_id = ? AND compte_credite_id BETWEEN 700 AND 799 AND YEAR(date_ecriture) = ?
    ");
    $stmt->execute([$s['id'], $exercice]);
    $ca = $stmt->fetchColumn();
    
    // Calcul des charges par section
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(montant), 0) as total
        FROM ECRITURES_COMPTABLES 
        WHERE section_analytique_id = ? AND compte_debite_id BETWEEN 600 AND 699 AND YEAR(date_ecriture) = ?
    ");
    $stmt->execute([$s['id'], $exercice]);
    $charges = $stmt->fetchColumn();
    
    $kpi_sections[$s['id']] = [
        'nom' => $s['libelle'],
        'code' => $s['code'],
        'type' => $s['type_section'],
        'ca' => $ca,
        'charges' => $charges,
        'resultat' => $ca - $charges,
        'taux_marge' => $ca > 0 ? ($ca - $charges) / $ca * 100 : 0
    ];
}

// Calcul du taux d'absorption budgétaire
$absorption = $pdo->prepare("
    SELECT 
        COALESCE(SUM(montant_ventes), 0) as budget_ventes,
        COALESCE(SUM(montant_achats + montant_charges), 0) as budget_consomme
    FROM BUDGETS_ANALYTIQUE 
    WHERE exercice = ? AND type_budget = 'PREVISIONNEL'
");
$absorption->execute([$exercice]);
$budget = $absorption->fetch();
$taux_absorption = $budget['budget_ventes'] > 0 ? ($budget['budget_consomme'] / $budget['budget_ventes']) * 100 : 0;

// Échéanciers à venir (7 jours)
$echeances = $pdo->prepare("
    SELECT e.*, t.raison_sociale 
    FROM ECHEANCIERS e
    LEFT JOIN TIERS t ON e.tiers_id = t.id
    WHERE e.date_echeance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND e.statut != 'PAYE'
    ORDER BY e.date_echeance ASC
");
$echeances->execute();
$echeances_data = $echeances->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <!-- Dashboard KPI -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6>Taux d'absorption budgétaire</h6>
                        <h2 class="<?= $taux_absorption <= 100 ? 'text-white' : 'text-warning' ?>">
                            <?= number_format($taux_absorption, 1) ?>%
                        </h2>
                        <small><?= $taux_absorption <= 100 ? '✅ Maîtrise des coûts' : '⚠️ Dépassement budgétaire' ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h6>Échéances à venir (7j)</h6>
                        <h2><?= count($echeances_data) ?></h2>
                        <small><?= number_format(array_sum(array_column($echeances_data, 'montant')), 0, ',', ' ') ?> F à régler</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6>ROI Global</h6>
                        <h2><?php 
                            $total_ca = array_sum(array_column($kpi_sections, 'ca'));
                            $total_charges = array_sum(array_column($kpi_sections, 'charges'));
                            $roi = $total_ca > 0 ? ($total_ca - $total_charges) / $total_ca * 100 : 0;
                            echo number_format($roi, 1) . '%';
                        ?></h2>
                        <small>Rentabilité globale</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6>CAF prévisionnelle</h6>
                        <h2><?= number_format($total_ca - $total_charges, 0, ',', ' ') ?> F</h2>
                        <small>Capacité d'autofinancement</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <ul class="nav nav-tabs" id="analytiqueTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#performances">📊 Performances</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#budget">💰 Gestion budgétaire</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#echeances">⏰ Échéanciers & alertes</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#kpi">📈 KPI & Objectifs</button></li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Onglet Performances -->
            <div class="tab-pane fade show active" id="performances">
                <div class="card">
                    <div class="card-header bg-primary text-white">Analyse de rentabilité par section</div>
                    <div class="card-body">
                        <canvas id="rentabiliteChart" height="200"></canvas>
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Section</th><th>Type</th><th class="text-end">CA (F)</th>
                                    <th class="text-end">Charges (F)</th><th class="text-end">Résultat (F)</th>
                                    <th>Marge</th><th>Performance</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($kpi_sections as $id => $s): ?>
                                    <tr class="<?= $s['resultat'] >= 0 ? 'table-success' : 'table-danger' ?>">
                                        <table><?= $s['nom'] ?> (<?= $s['code'] ?>)</td>
                                        <td class="text-center"><?= $s['type'] ?></td>
                                        <td class="text-end"><?= number_format($s['ca'], 0, ',', ' ') ?></td>
                                        <td class="text-end"><?= number_format($s['charges'], 0, ',', ' ') ?></td>
                                        <td class="text-end fw-bold"><?= number_format($s['resultat'], 0, ',', ' ') ?></td>
                                        <td class="text-center">
                                            <div class="progress" style="height:10px">
                                                <div class="progress-bar bg-<?= $s['taux_marge'] >= 20 ? 'success' : ($s['taux_marge'] >= 0 ? 'warning' : 'danger') ?>" 
                                                     style="width: <?= min(100, max(0, $s['taux_marge'])) ?>%">
                                                </div>
                                            </div>
                                            <?= number_format($s['taux_marge'], 1) ?>%
                                        </td>
                                        <td class="text-center">
                                            <?php if($s['resultat'] > 1000000): ?>
                                                <span class="badge bg-success">🏆 Excellence</span>
                                            <?php elseif($s['resultat'] > 0): ?>
                                                <span class="badge bg-info">📈 Rentable</span>
                                            <?php elseif($s['resultat'] == 0): ?>
                                                <span class="badge bg-secondary">⚖️ Équilibre</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">⚠️ Déficitaire</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            <table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglet Gestion budgétaire -->
            <div class="tab-pane fade" id="budget">
                <div class="card">
                    <div class="card-header bg-success text-white">Budget prévisionnel vs Réalisations</div>
                    <div class="card-body">
                        <form method="POST" action="enregistrer_budget.php" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label>Section analytique</label>
                                <select name="section_id" class="form-select">
                                    <?php foreach($sections as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= $s['code'] ?> - <?= $s['libelle'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Exercice</label>
                                <input type="number" name="exercice" class="form-control" value="<?= $exercice ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Mois</label>
                                <select name="mois" class="form-select">
                                    <?php for($i=1;$i<=12;$i++): ?>
                                        <option value="<?= $i ?>"><?= date('F', mktime(0,0,0,$i,1)) ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Montant ventes</label>
                                <input type="number" name="montant_ventes" class="form-control" step="100000">
                            </div>
                            <div class="col-md-2">
                                <label>Montant achats</label>
                                <input type="number" name="montant_achats" class="form-control" step="100000">
                            </div>
                            <div class="col-md-2">
                                <label>Charges</label>
                                <input type="number" name="montant_charges" class="form-control" step="100000">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn-omega mt-4">Enregistrer budget</button>
                            </div>
                        </form>
                        <canvas id="budgetChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Onglet Échéanciers -->
            <div class="tab-pane fade" id="echeances">
                <div class="card">
                    <div class="card-header bg-warning text-dark">Échéanciers et alertes</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Date échéance</th><th>Type</th><th>Tiers</th><th>Libellé</th>
                                    <th class="text-end">Montant</th><th>Statut</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($echeances_data as $e): 
                                        $jours = (strtotime($e['date_echeance']) - strtotime(date('Y-m-d'))) / 86400;
                                    ?>
                                    <tr class="<?= $jours <= 2 ? 'table-danger' : ($jours <= 5 ? 'table-warning' : '') ?>">
                                        <td class="text-center"><?= date('d/m/Y', strtotime($e['date_echeance'])) ?></td>
                                        <td class="text-center"><?= $e['type_echeance'] ?></td>
                                        <td><?= htmlspecialchars($e['raison_sociale'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($e['libelle']) ?></td>
                                        <td class="text-end"><?= number_format($e['montant'], 0, ',', ' ') ?> F</td>
                                        <td class="text-center">
                                            <span class="badge <?= $e['statut'] == 'EN_ATTENTE' ? 'bg-warning' : 'bg-danger' ?>">
                                                <?= $e['statut'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-success" onclick="envoyerAlerte(<?= $e['id'] ?>, <?= $e['montant'] ?>, '<?= addslashes($e['libelle']) ?>')">
                                                <i class="bi bi-bell"></i> Alerte
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Fonctionnalité d'alerte :</strong> Les alertes peuvent être envoyées par SMS, WhatsApp ou Email pour les échéances critiques (J-2, J-5).
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglet KPI -->
            <div class="tab-pane fade" id="kpi">
                <div class="card">
                    <div class="card-header bg-dark text-white">Indicateurs clés de performance</div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Return On Investment (ROI)</h6>
                                        <h3 class="text-primary"><?= number_format($roi, 1) ?>%</h3>
                                        <small>Objectif : > 15%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Marge brute moyenne</h6>
                                        <h3 class="text-success"><?= number_format(array_sum(array_column($kpi_sections, 'taux_marge')) / max(1, count($kpi_sections)), 1) ?>%</h3>
                                        <small>Objectif : > 25%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Taux d'endettement</h6>
                                        <h3 class="text-warning">0%</h3>
                                        <small>Objectif : < 50%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <canvas id="kpiChart" height="200" class="mt-4"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Graphique rentabilité
new Chart(document.getElementById('rentabiliteChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($kpi_sections, 'nom')) ?>,
        datasets: [
            {label: 'Chiffre d\'Affaires (F)', data: <?= json_encode(array_column($kpi_sections, 'ca')) ?>, backgroundColor: '#28a745'},
            {label: 'Charges (F)', data: <?= json_encode(array_column($kpi_sections, 'charges')) ?>, backgroundColor: '#dc3545'},
            {label: 'Résultat (F)', data: <?= json_encode(array_column($kpi_sections, 'resultat')) ?>, backgroundColor: '#0d6efd'}
        ]
    },
    options: { responsive: true, maintainAspectRatio: true }
});

// Graphique budget
new Chart(document.getElementById('budgetChart'), {
    type: 'line',
    data: { labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'], datasets: [] },
    options: { responsive: true }
});

function envoyerAlerte(id, montant, libelle) {
    if(confirm(`Envoyer une alerte pour l'échéance ${libelle} (${montant.toLocaleString()} F) ?`)) {
        fetch(`envoyer_alerte.php?id=${id}`).then(r => r.json()).then(data => {
            alert(data.message);
        });
    }
}
</script>

<?php include 'inc_footer.php'; ?>
