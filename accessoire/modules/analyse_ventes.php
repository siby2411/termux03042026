<?php
// /var/www/piece_auto/modules/analyse_ventes.php

// Inclusions et Sécurité
include_once '../config/Database.php';
// check_role('Vendeur', 'Analyse des Ventes'); // Activer si les rôles sont configurés
include '../includes/header.php';
$page_title = "Analyse des Ventes et Marges";

$database = new Database();
$db = $database->getConnection(); 
$message_status = "";

// --- 1. FONCTIONS D'ANALYSE SQL (Calculs de performance) ---

// 1.1 Total des Ventes (HT) par mois
$query_sales_by_month = "
    SELECT 
        DATE_FORMAT(date_vente, '%Y-%m') AS mois, 
        SUM(total_ht) AS total_ventes_ht 
    FROM VENTES
    GROUP BY mois
    ORDER BY mois DESC
    LIMIT 6";
$sales_by_month = $db->query($query_sales_by_month)->fetchAll(PDO::FETCH_ASSOC);

// 1.2 Calcul du Bénéfice Brut Global
$query_gross_profit = "
    SELECT 
        SUM(LV.quantite * LV.prix_unitaire_vendu) AS ChiffreAffaires,
        SUM(LV.quantite * P.prix_achat) AS CoutVentes,
        (SUM(LV.quantite * LV.prix_unitaire_vendu) - SUM(LV.quantite * P.prix_achat)) AS BeneficeBrut
    FROM LIGNES_VENTE LV
    JOIN PIECES P ON LV.id_piece = P.id_piece";
$gross_profit = $db->query($query_gross_profit)->fetch(PDO::FETCH_ASSOC);


// 1.3 Top 5 des Pièces les plus Vendues (en Quantité)
$query_top_pieces = "
    SELECT 
        P.nom_piece, 
        MA.nom_marque,
        SUM(LV.quantite) AS total_quantite_vendue
    FROM LIGNES_VENTE LV
    JOIN PIECES P ON LV.id_piece = P.id_piece
    JOIN MARQUES_AUTO MA ON P.id_marque = MA.id_marque
    GROUP BY P.id_piece, P.nom_piece, MA.nom_marque
    ORDER BY total_quantite_vendue DESC
    LIMIT 5";
$top_pieces = $db->query($query_top_pieces)->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-chart-line"></i> Tableau de Bord Analytique</h2>
        <p class="text-muted">Vue synthétique des performances de vente et de la rentabilité.</p>
    </div>
</div>

<div class="row mb-5">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Chiffre d'Affaires Total (HT)</h5>
                <p class="card-text fs-3"><?= number_format($gross_profit['ChiffreAffaires'] ?? 0, 2, ',', ' ') ?> €</p>
                <small>Basé sur <?= count($sales_by_month) ?> derniers mois.</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Bénéfice Brut Total</h5>
                <p class="card-text fs-3"><?= number_format($gross_profit['BeneficeBrut'] ?? 0, 2, ',', ' ') ?> €</p>
                <small>Ventes HT - Coût des Pièces Vendues</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Marge Brute Moyenne</h5>
                <?php 
                    $ca = (float)($gross_profit['ChiffreAffaires'] ?? 0);
                    $marge = (float)($gross_profit['BeneficeBrut'] ?? 0);
                    $marge_percent = ($ca > 0) ? ($marge / $ca) * 100 : 0;
                ?>
                <p class="card-text fs-3"><?= number_format($marge_percent, 1, ',', ' ') ?> %</p>
                <small>Ratio Bénéfice / Chiffre d'Affaires</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-md-8">
        <div class="card p-4">
            <h4 class="card-title"><i class="fas fa-chart-bar"></i> Ventes Mensuelles (HT)</h4>
            <canvas id="salesChart" style="max-height: 400px;"></canvas>
            
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <h4 class="card-title"><i class="fas fa-trophy"></i> Top 5 Pièces (Quantité)</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Pièce</th>
                        <th>Marque</th>
                        <th>Qté</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($top_pieces)): ?>
                        <?php foreach($top_pieces as $p): ?>
                            <tr>
                                <td><?= $p['nom_piece'] ?></td>
                                <td><?= $p['nom_marque'] ?></td>
                                <td class="fw-bold text-end"><?= $p['total_quantite_vendue'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-muted text-center">Aucune donnée de vente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données générées par PHP pour Chart.js
    const salesData = <?= json_encode($sales_by_month); ?>;

    // Préparation des Labels (Mois) et des Données (Montants)
    // Nous inversons l'ordre pour que le mois le plus récent soit à droite
    const labels = salesData.map(item => item.mois).reverse();
    const dataValues = salesData.map(item => parseFloat(item.total_ventes_ht)).reverse();

    const ctx = document.getElementById('salesChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventes HT (€)',
                data: dataValues,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Montant des Ventes HT (€)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php 
include '../includes/footer.php'; 
?>
