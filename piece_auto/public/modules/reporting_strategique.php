<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$page_title = "Reporting Stratégique";
include '../../includes/header.php';

// 1. Données pour le graphique linéaire (7 derniers jours)
$ventes_jours = $db->query("SELECT DATE(date_vente) as jour, SUM(total_commande) as total 
                            FROM COMMANDE_VENTE 
                            WHERE date_vente >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                            GROUP BY DATE(date_vente) ORDER BY jour ASC")->fetchAll(PDO::FETCH_ASSOC);

// 2. Données pour le camembert (Répartition par catégorie)
$cat_data = $db->query("SELECT p.categorie, SUM(lv.quantite) as qte 
                        FROM LIGNE_VENTE lv 
                        JOIN PIECES p ON lv.id_piece = p.id_piece 
                        GROUP BY p.categorie")->fetchAll(PDO::FETCH_ASSOC);

// Préparation des labels et données pour JS
$labels_jours = json_encode(array_column($ventes_jours, 'jour'));
$data_jours = json_encode(array_column($ventes_jours, 'total'));
$labels_cat = json_encode(array_column($cat_data, 'categorie'));
$data_cat = json_encode(array_column($cat_data, 'qte'));
?>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold">Évolution du Chiffre d'Affaires (7 jours)</div>
            <div class="card-body">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold">Top Catégories (Volume)</div>
            <div class="card-body">
                <canvas id="catChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Graphique Linéaire
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: <?= $labels_jours ?>,
        datasets: [{
            label: 'Ventes en FCFA',
            data: <?= $data_jours ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true,
            tension: 0.3
        }]
    }
});

// Graphique Camembert
new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
        labels: <?= $labels_cat ?>,
        datasets: [{
            data: <?= $data_cat ?>,
            backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#fd7e14']
        }]
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
