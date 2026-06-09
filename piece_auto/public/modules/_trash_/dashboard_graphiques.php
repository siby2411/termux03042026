<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Dashboard Graphique - Analytics";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Données pour les graphiques
$ca_mensuel = [];
$charges_mensuelles = [];
$mois = [];

for ($i = 1; $i <= 12; $i++) {
    $mois[] = date('F', mktime(0, 0, 0, $i, 1));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND compte_credite_id BETWEEN 700 AND 799");
    $stmt->execute([$i]);
    $ca_mensuel[] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND compte_debite_id BETWEEN 600 AND 699");
    $stmt->execute([$i]);
    $charges_mensuelles[] = $stmt->fetchColumn();
}

// Agrégations
$total_ca = array_sum($ca_mensuel);
$total_charges = array_sum($charges_mensuelles);
$resultat = $total_ca - $total_charges;

// Top 5 comptes les plus utilisés
$top_comptes = $pdo->query("
    SELECT compte_id, intitule_compte, COUNT(*) as usage_count
    FROM (
        SELECT compte_debite_id as compte_id FROM ECRITURES_COMPTABLES
        UNION ALL
        SELECT compte_credite_id FROM ECRITURES_COMPTABLES
    ) as mouvements
    JOIN PLAN_COMPTABLE_UEMOA ON compte_id = mouvements.compte_id
    WHERE compte_id IS NOT NULL
    GROUP BY compte_id, intitule_compte
    ORDER BY usage_count DESC
    LIMIT 5
")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Tableau de bord analytique</h5>
                <small>Indicateurs de performance - Exercice <?= date('Y') ?></small>
            </div>
            <div class="card-body">
                <!-- KPI Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center border-primary">
                            <div class="card-body">
                                <h6>Chiffre d'Affaires</h6>
                                <h3 class="text-primary"><?= number_format($total_ca, 0, ',', ' ') ?> F</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-danger">
                            <div class="card-body">
                                <h6>Total Charges</h6>
                                <h3 class="text-danger"><?= number_format($total_charges, 0, ',', ' ') ?> F</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <h6>Résultat Net</h6>
                                <h3 class="<?= $resultat >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($resultat), 0, ',', ' ') ?> F
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <h6>Nombre d'écritures</h6>
                                <h3><?= $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES")->fetchColumn() ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Graphique CA vs Charges -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6>Évolution mensuelle (en milliers FCFA)</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlyChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top comptes -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6>Top 5 comptes les plus utilisés</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach($top_comptes as $c): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><strong><?= $c['compte_id'] ?></strong> - <?= htmlspecialchars($c['intitule_compte']) ?></span>
                                        <span class="badge bg-secondary"><?= $c['usage_count'] ?> écritures</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?= min(100, ($c['usage_count'] / max(array_column($top_comptes, 'usage_count'))) * 100) ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6>Ratios financiers</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><td>Taux de marge :</td><td class="text-end fw-bold"><?= $total_ca > 0 ? number_format(($resultat / $total_ca) * 100, 2) : 0 ?>%</td></tr>
                                    <tr><td>Ratio charges/CA :</td><td class="text-end fw-bold"><?= $total_ca > 0 ? number_format(($total_charges / $total_ca) * 100, 2) : 0 ?>%</td></tr>
                                    <tr><td>Nombre de jours d'écriture :</td><td class="text-end fw-bold"><?= ceil($pdo->query("SELECT COUNT(DISTINCT date_ecriture) FROM ECRITURES_COMPTABLES")->fetchColumn() / 30) ?> jours/mois</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($mois) ?>,
        datasets: [
            {
                label: 'Chiffre d\'Affaires',
                data: <?= json_encode($ca_mensuel) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Charges',
                data: <?= json_encode($charges_mensuelles) ?>,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + new Intl.NumberFormat().format(context.raw) + ' FCFA';
                    }
                }
            }
        }
    }
});
</script>

<?php include 'inc_footer.php'; ?>
