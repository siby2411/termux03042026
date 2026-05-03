<?php
require_once '../header.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$pdo = getPDO();
$year = $_GET['year'] ?? date('Y');

// Statistiques des ventes mensuelles
$ventes_mensuelles = $pdo->query("
    SELECT 
        MONTH(date_vente) as mois,
        COUNT(*) as nb_ventes,
        SUM(total_ttc) as ca
    FROM ventes 
    WHERE YEAR(date_vente) = $year
    GROUP BY MONTH(date_vente)
    ORDER BY mois
")->fetchAll();

// Top produits
$top_produits = $pdo->query("
    SELECT p.nom, SUM(vl.quantite) as total_vendu, SUM(vl.total_ht) as ca
    FROM ventes_lignes vl
    JOIN produits p ON vl.produit_id = p.id
    JOIN ventes v ON vl.vente_id = v.id
    WHERE YEAR(v.date_vente) = $year
    GROUP BY p.id
    ORDER BY total_vendu DESC
    LIMIT 10
")->fetchAll();

// Évolution du stock
$stock_evolution = $pdo->query("
    SELECT p.nom, p.stock_actuel, p.stock_min
    FROM produits p
    ORDER BY p.stock_actuel ASC
    LIMIT 10
")->fetchAll();

// Chiffre d'affaires par mois
$ca_par_mois = array_fill(1, 12, 0);
$nb_ventes_par_mois = array_fill(1, 12, 0);
foreach ($ventes_mensuelles as $v) {
    $ca_par_mois[$v['mois']] = $v['ca'];
    $nb_ventes_par_mois[$v['mois']] = $v['nb_ventes'];
}

$mois_labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chart-line me-2"></i>Statistiques et Analyses</h2>
    <form method="get" class="d-inline">
        <select name="year" class="form-select" onchange="this.form.submit()">
            <?php for ($y = date('Y')-2; $y <= date('Y'); $y++): ?>
            <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Évolution mensuelle du CA (<?= $year ?>)</h5>
            </div>
            <div class="card-body">
                <canvas id="caChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Top 10 produits les plus vendus</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Produit</th><th>Quantité</th><th>CA</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_produits as $p): ?>
                            <tr>
                                <td><?= escape($p['nom']) ?></td>
                                <td><?= $p['total_vendu'] ?></td>
                                <td><?= formatMoney($p['ca']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Alertes stock</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Produit</th><th>Stock actuel</th><th>Stock minimum</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stock_evolution as $p): ?>
                            <tr class="<?= $p['stock_actuel'] <= $p['stock_min'] ? 'table-danger' : '' ?>">
                                <td><?= escape($p['nom']) ?></td>
                                <td><?= $p['stock_actuel'] ?></td>
                                <td><?= $p['stock_min'] ?></td>
                                <td>
                                    <?php if ($p['stock_actuel'] <= $p['stock_min']): ?>
                                        <span class="badge bg-danger">Rupture imminente</span>
                                    <?php elseif ($p['stock_actuel'] <= $p['stock_min'] * 2): ?>
                                        <span class="badge bg-warning">Stock faible</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">OK</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Indicateurs clés</h5>
            </div>
            <div class="card-body">
                <?php
                $total_ca = $pdo->query("SELECT SUM(total_ttc) FROM ventes WHERE YEAR(date_vente) = $year")->fetchColumn();
                $total_ventes = $pdo->query("SELECT COUNT(*) FROM ventes WHERE YEAR(date_vente) = $year")->fetchColumn();
                $panier_moyen = $total_ventes > 0 ? $total_ca / $total_ventes : 0;
                ?>
                <div class="row text-center">
                    <div class="col-md-4">
                        <h3><?= formatMoney($total_ca) ?></h3>
                        <small>CA total <?= $year ?></small>
                    </div>
                    <div class="col-md-4">
                        <h3><?= $total_ventes ?></h3>
                        <small>Ventes réalisées</small>
                    </div>
                    <div class="col-md-4">
                        <h3><?= formatMoney($panier_moyen) ?></h3>
                        <small>Panier moyen</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('caChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($mois_labels) ?>,
        datasets: [{
            label: 'Chiffre d\'affaires (FCFA)',
            data: <?= json_encode(array_values($ca_par_mois)) ?>,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52,152,219,0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                    }
                }
            }
        }
    }
});
</script>
<?php require_once '../footer.php'; ?>
