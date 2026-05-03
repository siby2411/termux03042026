<?php
// /dashboard_pilote.php
$page_title = "Tableau de Bord Stratégique";
// La connexion est désormais gérée par la structure que vous avez confirmée
include_once __DIR__ . '/config/db.php'; 
include_once __DIR__ . '/includes/header.php'; // On suppose que le header contient le début du HTML et le style

$database = new Database();
$db = $database->getConnection(); // L'objet PDO est dans $db

// Période d'analyse : Derniers 30 jours
$date_fin = date('Y-m-d');
$date_debut = date('Y-m-d', strtotime('-30 days'));

$kpis = [
    'ChiffreAffaires' => 0.00,
    'MargeBrute' => 0.00,
    'CoutDesVentes' => 0.00,
    'ResultatNet' => 0.00, 
    'ROI_Estime' => 0.00,
    // Nouveau KPI Stock
    'ValorisationStock' => 0.00 
];
$frais_fixes_estimes = 15000.00; // Exemple de frais fixes

try {
    // ----------------------------------------------------------------------
    // 1. CALCULS DES KPI PRINCIPAUX (CA, Marge Brute, Coût des Ventes)
    //    *** UTILISATION DE LA VUE V_Marge_Ventes ***
    // ----------------------------------------------------------------------
    $query_kpi = "
        SELECT 
            COALESCE(SUM(MontantCA), 0) AS CA_Total,
            COALESCE(SUM(MontantCDV), 0) AS CDV_Total,
            COALESCE(SUM(MargeBrute), 0) AS MargeBrute_Total
        FROM V_Marge_Ventes
        WHERE DateCommande BETWEEN :date_debut AND :date_fin
    ";
    $stmt_kpi = $db->prepare($query_kpi);
    $stmt_kpi->bindParam(':date_debut', $date_debut);
    $stmt_kpi->bindParam(':date_fin', $date_fin);
    $stmt_kpi->execute();
    $result_kpi = $stmt_kpi->fetch(PDO::FETCH_ASSOC);

    $kpis['ChiffreAffaires'] = $result_kpi['CA_Total'];
    $kpis['CoutDesVentes'] = $result_kpi['CDV_Total'];
    $kpis['MargeBrute'] = $result_kpi['MargeBrute_Total']; // Directement issu de la vue
    
    // Calculs dérivés
    $kpis['ResultatNet'] = $kpis['MargeBrute'] - $frais_fixes_estimes;
    $roi_estim = ($kpis['CoutDesVentes'] > 0) 
        ? (($kpis['MargeBrute'] / $kpis['CoutDesVentes']) * 100) 
        : 0;
    $kpis['ROI_Estime'] = round($roi_estim, 1);
    
    // ----------------------------------------------------------------------
    // 2. CALCUL DE LA VALORISATION DU STOCK ET CUMP MOYEN GLOBAL
    //    *** UTILISATION DE LA VUE V_Valorisation_Stock ***
    // ----------------------------------------------------------------------
    $query_stock = "SELECT ValeurTotaleStock FROM V_Valorisation_Stock";
    $kpis['ValorisationStock'] = $db->query($query_stock)->fetchColumn() ?? 0.00;

    $cump_moyen = 0;
    $query_cump = "SELECT AVG(CUMP) AS CUMP_Moyen FROM Produits WHERE StockActuel > 0";
    $stmt_cump = $db->query($query_cump);
    $result_cump = $stmt_cump->fetch(PDO::FETCH_ASSOC);
    if ($result_cump && $result_cump['CUMP_Moyen'] !== null) {
        $cump_moyen = $result_cump['CUMP_Moyen'];
    }

    // L'analyse ROI par Produit reste basée sur la table Produits
    $roi_detail = [];
    $query_roi = "
        SELECT 
            P.Nom AS Produit,
            P.PrixVente,
            P.CUMP,
            ((P.PrixVente - P.CUMP) / P.CUMP) * 100 AS ROI_Produit
        FROM Produits P
        WHERE P.CUMP > 0
        ORDER BY ROI_Produit DESC
        LIMIT 5
    ";
    $stmt_roi = $db->query($query_roi);
    $roi_detail = $stmt_roi->fetchAll(PDO::FETCH_ASSOC);


    // ----------------------------------------------------------------------
    // 3. DONNÉES POUR LES GRAPHIQUES (Tendance Journalière)
    //    *** UTILISATION DE LA VUE V_Marge_Ventes ***
    // ----------------------------------------------------------------------
    $ca_daily_data = [];
    $marge_daily_data = [];
    $dates_labels = [];
    $query_daily = "
        SELECT 
            DATE(DateCommande) AS Jour,
            COALESCE(SUM(MontantCA), 0) AS CA_Jour,
            COALESCE(SUM(MargeBrute), 0) AS Marge_Jour
        FROM V_Marge_Ventes
        WHERE DateCommande BETWEEN :date_debut AND :date_fin
        GROUP BY Jour
        ORDER BY Jour ASC
    ";
    $stmt_daily = $db->prepare($query_daily);
    $stmt_daily->bindParam(':date_debut', $date_debut);
    $stmt_daily->bindParam(':date_fin', $date_fin);
    $stmt_daily->execute();
    $daily_results = $stmt_daily->fetchAll(PDO::FETCH_ASSOC);

    // Initialisation des 30 jours pour éviter les trous (LOGIQUE INCHANGÉE)
    $current_date = new DateTime($date_debut);
    $end_date_obj = new DateTime($date_fin);
    $data_map = array_column($daily_results, null, 'Jour');

    while ($current_date <= $end_date_obj) {
        $day_str = $current_date->format('Y-m-d');
        $dates_labels[] = $current_date->format('d/m');
        
        if (isset($data_map[$day_str])) {
            $ca_daily_data[] = round($data_map[$day_str]['CA_Jour'], 0);
            $marge_daily_data[] = round($data_map[$day_str]['Marge_Jour'], 0);
        } else {
            $ca_daily_data[] = 0;
            $marge_daily_data[] = 0;
        }
        $current_date->modify('+1 day');
    }

    // 4. DONNÉES POUR LES GRAPHIQUES (Répartition Marge - Top 5 Produits)
    //    *** UTILISATION DE LA VUE V_Marge_Ventes pour plus de cohérence ***
    $produits_labels = [];
    $marges_produits_data = [];
    $query_marge_produit = "
        SELECT 
            P.Nom AS Produit,
            COALESCE(SUM(VMV.MargeBrute), 0) AS Marge_Total
        FROM V_Marge_Ventes VMV
        JOIN Produits P ON VMV.ProduitID = P.ProduitID
        GROUP BY P.Nom
        ORDER BY Marge_Total DESC
        LIMIT 5
    ";
    $stmt_marge_produit = $db->query($query_marge_produit);
    $marge_results = $stmt_marge_produit->fetchAll(PDO::FETCH_ASSOC);

    $produits_labels = array_column($marge_results, 'Produit');
    $marges_produits_data = array_column($marge_results, 'Marge_Total');


} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur de chargement du Dashboard. Vérifiez les tables Commandes, DetailsCommande, Produits et les VUES V_Marge_Ventes et V_Valorisation_Stock. " . $e->getMessage() . "</div>";
    // Les tableaux resteront vides ou avec les valeurs initiales (0.00)
}
?>

<h1 class="mt-4 text-center"><i class="fas fa-tachometer-alt me-2"></i> Tableau de Bord Stratégique</h1>
<p class="text-muted text-center">Indicateurs Clés de Performance et Tendances (Période : 30 derniers jours).</p>
<hr class="mb-5">

<?php
// Note: Ces fonctions doivent être disponibles si non définies dans header.php ou un fichier inclus.
if (!function_exists('number_format')) {
    function format_currency($amount) {
        return number_format($amount, 2, ',', ' ') . ' €';
    }
}
?>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-lg text-white bg-success">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <div class="fs-6 text-uppercase">Chiffre d'Affaires</div>
                        <div class="fs-3 fw-bold"><?= number_format($kpis['ChiffreAffaires'], 0, ',', ' ') ?> €</div>
                    </div>
                    <div class="col-4 text-end"><i class="fas fa-euro-sign fa-3x"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-lg text-white bg-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <div class="fs-6 text-uppercase">Marge Brute HT</div>
                        <div class="fs-3 fw-bold"><?= number_format($kpis['MargeBrute'], 0, ',', ' ') ?> €</div>
                    </div>
                    <div class="col-4 text-end"><i class="fas fa-hand-holding-usd fa-3x"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <?php $bg_resultat = ($kpis['ResultatNet'] >= 0) ? 'bg-info' : 'bg-danger'; ?>
        <div class="card border-0 shadow-lg text-white <?= $bg_resultat ?>">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <div class="fs-6 text-uppercase">Résultat Net Est.</div>
                        <div class="fs-3 fw-bold"><?= number_format($kpis['ResultatNet'], 0, ',', ' ') ?> €</div>
                    </div>
                    <div class="col-4 text-end"><i class="fas fa-balance-scale fa-3x"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <?php $bg_roi = ($kpis['ROI_Estime'] >= 50) ? 'bg-dark' : 'bg-warning'; ?>
        <div class="card border-0 shadow-lg text-white <?= $bg_roi ?>">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <div class="fs-6 text-uppercase">ROI (Est.)</div>
                        <div class="fs-3 fw-bold"><?= number_format($kpis['ROI_Estime'], 1, ',', ' ') ?> %</div>
                    </div>
                    <div class="col-4 text-end"><i class="fas fa-percent fa-3x"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 mb-5">
    
    <div class="col-lg-6 col-md-6 mb-4">
        <div class="card border-0 shadow-lg text-white bg-secondary">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <div class="fs-6 text-uppercase">Coût des Ventes (CDV)</div>
                        <div class="fs-3 fw-bold"><?= number_format($kpis['CoutDesVentes'], 0, ',', ' ') ?> €</div>
                    </div>
                    <div class="col-4 text-end"><i class="fas fa-boxes fa-3x"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <div class="card border-0 shadow-lg text-white bg-warning">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <div class="fs-6 text-uppercase">Valorisation des Stocks (CUMP)</div>
                        <div class="fs-3 fw-bold"><?= number_format($kpis['ValorisationStock'], 0, ',', ' ') ?> €</div>
                    </div>
                    <div class="col-4 text-end"><i class="fas fa-warehouse fa-3x"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    
    <div class="col-lg-8 mb-4">
        <div class="card shadow-lg h-100">
            <div class="card-header bg-secondary text-white fw-bold"><i class="fas fa-chart-line me-2"></i> Tendance CA & Marge (30 Jours)</div>
            <div class="card-body">
                <canvas id="caMargeChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card shadow-lg h-100">
            <div class="card-header bg-secondary text-white fw-bold"><i class="fas fa-chart-pie me-2"></i> Marge Brute par Produit (Top 5)</div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="margeProduitChart"></canvas>
            </div>
        </div>
    </div>
    
</div>

<div class="row mt-5">
    <h2 class="mb-4"><i class="fas fa-hand-holding-usd me-2 text-info"></i> Rentabilité et Coûts</h2>
    
    <div class="col-lg-4 mb-4">
        <div class="card shadow-lg h-100 border-info border-3">
            <div class="card-header bg-info text-white fw-bold">CUMP Moyen Global</div>
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-info">
                    <?= number_format($cump_moyen, 2, ',', ' ') ?> €
                </div>
                <p class="text-muted mt-2">Coût Unitaire Moyen Pondéré de l'ensemble du stock.</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8 mb-4">
        <div class="card shadow-lg h-100">
            <div class="card-header bg-secondary text-white fw-bold"><i class="fas fa-percent me-2"></i> ROI (Marge brute / CUMP) par Produit</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Produit</th>
                                <th class="text-end">Prix Vente (€)</th>
                                <th class="text-end">CUMP (€)</th>
                                <th class="text-end text-success">ROI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($roi_detail)): ?>
                                <?php foreach ($roi_detail as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['Produit']) ?></td>
                                    <td class="text-end"><?= number_format($row['PrixVente'], 2, ',', ' ') ?></td>
                                    <td class="text-end"><?= number_format($row['CUMP'], 2, ',', ' ') ?></td>
                                    <td class="text-end fw-bold text-success">
                                        <?= number_format($row['ROI_Produit'], 1, ',', ' ') ?> %
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted p-3">Aucun produit avec CUMP positif trouvé.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ---------------------------------------------
    // 1. Tendance CA vs Marge (Graphique Ligne)
    // ---------------------------------------------
    const caMargeCtx = document.getElementById('caMargeChart').getContext('2d');
    
    const caData = <?= json_encode($ca_daily_data) ?>;
    const margeData = <?= json_encode($marge_daily_data) ?>;
    const labels = <?= json_encode($dates_labels) ?>;
    
    new Chart(caMargeCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Chiffre d\'Affaires (€)',
                    data: caData,
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: false,
                    tension: 0.1
                },
                {
                    label: 'Marge Brute (€)',
                    data: margeData,
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: false,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'Performance Journalière (CA vs Marge)' }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Montant (€)' } },
                x: { title: { display: true, text: 'Date' } }
            }
        }
    });
    
    // ---------------------------------------------
    // 2. Répartition des Marges (Graphique Donut)
    // ---------------------------------------------
    const margeProduitCtx = document.getElementById('margeProduitChart').getContext('2d');
    
    const produitsLabels = <?= json_encode($produits_labels) ?>;
    const margesData = <?= json_encode($marges_produits_data) ?>;

    new Chart(margeProduitCtx, {
        type: 'doughnut',
        data: {
            labels: produitsLabels,
            datasets: [{
                data: margesData,
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#17a2b8', '#6c757d'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: false }
            }
        }
    });
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
