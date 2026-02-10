<?php
// /var/www/piece_auto/public/modules/tableau_de_bord.php
$page_title = "Tableau de Bord Analytique";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Statistiques Globales (Correction : total_commande)
    $stats = $db->query("SELECT 
        COUNT(*) as nb_ventes, 
        SUM(total_commande) as ca_total,
        AVG(total_commande) as panier_moyen
        FROM COMMANDE_VENTE")->fetch(PDO::FETCH_ASSOC);

    // 2. Top 5 des pièces les plus vendues (Analyse de volume)
    $query_top = "SELECT p.nom_piece, SUM(dv.quantite_vendue) as total_qte
                  FROM DETAIL_VENTE dv
                  JOIN PIECES p ON dv.id_piece = p.id_piece
                  GROUP BY p.id_piece
                  ORDER BY total_qte DESC
                  LIMIT 5";
    $top_pieces = $db->query($query_top)->fetchAll(PDO::FETCH_ASSOC);

    // 3. Données pour le graphique (Ventes des 30 derniers jours par groupe de date)
    $query_chart = "SELECT DATE(date_commande) as jour, SUM(total_commande) as total 
                    FROM COMMANDE_VENTE 
                    WHERE date_commande >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(date_commande)
                    ORDER BY jour ASC";
    $chart_data = $db->query($query_chart)->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $totals = [];
    foreach($chart_data as $data) {
        $labels[] = date('d/m', strtotime($data['jour']));
        $totals[] = $data['total'];
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur de chargement des données : ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-bar text-primary"></i> <?= $page_title ?></h1>
        <button onclick="window.location.reload()" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-sync"></i> Actualiser
        </button>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-gradient-primary text-white shadow p-3 border-0" style="background: linear-gradient(45deg, #0d6efd, #0043a8);">
                <div class="small text-uppercase">Chiffre d'Affaires (30j)</div>
                <div class="display-6 fw-bold"><?= number_format($stats['ca_total'] ?? 0, 2, ',', ' ') ?> €</div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card bg-gradient-info text-white shadow p-3 border-0" style="background: linear-gradient(45deg, #0dcaf0, #0aa2c0);">
                <div class="small text-uppercase">Volume de Ventes</div>
                <div class="display-6 fw-bold"><?= $stats['nb_ventes'] ?? 0 ?> <small class="h4">Commandes</small></div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card bg-gradient-success text-white shadow p-3 border-0" style="background: linear-gradient(45deg, #198754, #105a38);">
                <div class="small text-uppercase">Panier Moyen</div>
                <div class="display-6 fw-bold"><?= number_format($stats['panier_moyen'] ?? 0, 2, ',', ' ') ?> €</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Évolution des revenus (30 derniers jours)</div>
                <div class="card-body">
                    <canvas id="analytiqueChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Top 5 Pièces Vendues</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach($top_pieces as $tp): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($tp['nom_piece']) ?>
                            <span class="badge bg-primary rounded-pill"><?= $tp['total_qte'] ?> unités</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('analytiqueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Revenus (€)',
            data: <?= json_encode($totals) ?>,
            backgroundColor: '#0d6efd',
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
