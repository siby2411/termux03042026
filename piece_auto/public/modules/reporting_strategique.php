<?php
// /var/www/piece_auto/public/modules/reporting_strategique.php
$page_title = "Reporting Stratégique";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. RÉPARTITION DU CA PAR CATÉGORIE (Pie Chart)
    $query_cat = "SELECT c.nom_categorie, SUM(dv.quantite_vendue * dv.prix_vente_unitaire) as total_cat
                  FROM DETAIL_VENTE dv
                  JOIN PIECES p ON dv.id_piece = p.id_piece
                  LEFT JOIN CATEGORIES c ON p.id_categorie = c.id_categorie
                  GROUP BY c.nom_categorie";
    $cat_data = $db->query($query_cat)->fetchAll(PDO::FETCH_ASSOC);

    // 2. ANALYSE MENSUELLE DE L'ANNÉE EN COURS (Line Chart)
    $query_months = "SELECT MONTH(date_commande) as mois, SUM(total_commande) as total
                     FROM COMMANDE_VENTE
                     WHERE YEAR(date_commande) = YEAR(CURDATE())
                     GROUP BY MONTH(date_commande)
                     ORDER BY mois";
    $month_data = $db->query($query_months)->fetchAll(PDO::FETCH_ASSOC);

    // Préparation des mois (1-12)
    $ventes_mensuelles = array_fill(1, 12, 0);
    foreach($month_data as $m) { $ventes_mensuelles[$m['mois']] = (float)$m['total']; }

    // 3. TOP CLIENTS (Stratégie de fidélisation)
    $query_clients = "SELECT c.nom, c.prenom, SUM(cv.total_commande) as ca_client
                      FROM COMMANDE_VENTE cv
                      JOIN CLIENTS c ON cv.id_client = c.id_client
                      GROUP BY cv.id_client
                      ORDER BY ca_client DESC LIMIT 5";
    $top_clients = $db->query($query_clients)->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur stratégique : ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="fas fa-brain text-info"></i> Analyse & Stratégie</h1>
            <p class="text-muted">Analyse approfondie des performances pour optimiser vos achats et vos ventes.</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-outline-primary" onclick="window.print()"><i class="fas fa-print"></i> Rapport PDF</button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Poids du CA par Catégorie</div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Performance Mensuelle (<?= date('Y') ?>)</div>
                <div class="card-body">
                    <canvas id="growthChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Top 5 Clients Stratégiques</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Client</th><th class="text-end">CA Généré</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($top_clients as $tc): ?>
                            <tr>
                                <td><?= htmlspecialchars($tc['prenom'] . ' ' . $tc['nom']) ?></td>
                                <td class="text-end fw-bold"><?= number_format($tc['ca_client'], 2, ',', ' ') ?> €</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card bg-info text-white shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold"><i class="fas fa-lightbulb"></i> Conseil Stratégique</h5>
                    <p>D'après vos données actuelles :</p>
                    <ul>
                        <li>Votre catégorie dominante nécessite une attention particulière sur les stocks.</li>
                        <li>Les clients du Top 5 représentent une part critique de votre revenu.</li>
                    </ul>
                    <hr>
                    <small>Utilisez ces données pour négocier des remises de volume avec vos fournisseurs.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Graphique Catégories
const catCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(catCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($cat_data, 'nom_categorie')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($cat_data, 'total_cat')) ?>,
            backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6610f2', '#fd7e14']
        }]
    }
});

// Graphique Croissance
const growthCtx = document.getElementById('growthChart').getContext('2d');
new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
            label: 'Chiffre d\'Affaires (€)',
            data: <?= json_encode(array_values($ventes_mensuelles)) ?>,
            borderColor: '#0dcaf0',
            backgroundColor: 'rgba(13, 202, 240, 0.1)',
            fill: true,
            tension: 0.4
        }]
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
