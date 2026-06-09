<?php
require_once __DIR__ . '/../includes/db.php';
$page_title = "Statistiques Élite - OMEGA";
include "layout.php";

$stats = $pdo->query("
    SELECT 
        SUM(CASE WHEN compte_credite_id LIKE '7%' THEN montant ELSE 0 END) as ca,
        SUM(CASE WHEN compte_debite_id LIKE '6%' THEN montant ELSE 0 END) as charges
    FROM ECRITURES_COMPTABLES 
    WHERE YEAR(date_ecriture) = 2026
")->fetch(PDO::FETCH_ASSOC);

$ca = $stats['ca'] ?? 0;
$charges = $stats['charges'] ?? 0;
$profit = $ca - $charges;
?>

<div class="form-centered">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card omega-card bg-primary text-white p-4 border-0 position-relative overflow-hidden">
                <small class="text-uppercase fw-bold opacity-75">Chiffre d'Affaires</small>
                <h2 class="mb-0 fw-bold"><?= number_format($ca, 0, ',', ' ') ?> F</h2>
                <i class="bi bi-cash-stack position-absolute end-0 bottom-0 m-2 opacity-25" style="font-size: 3rem;"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card omega-card bg-danger text-white p-4 border-0 position-relative overflow-hidden">
                <small class="text-uppercase fw-bold opacity-75">Charges Totales</small>
                <h2 class="mb-0 fw-bold"><?= number_format($charges, 0, ',', ' ') ?> F</h2>
                <i class="bi bi-cart-x position-absolute end-0 bottom-0 m-2 opacity-25" style="font-size: 3rem;"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card omega-card bg-success text-white p-4 border-0 position-relative overflow-hidden">
                <small class="text-uppercase fw-bold opacity-75">Résultat Net</small>
                <h2 class="mb-0 fw-bold"><?= number_format($profit, 0, ',', ' ') ?> F</h2>
                <i class="bi bi-graph-up-arrow position-absolute end-0 bottom-0 m-2 opacity-25" style="font-size: 3rem;"></i>
            </div>
        </div>
    </div>

    
    <div class="card omega-card border-0 p-5 shadow-lg bg-white">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-dark fw-bold mb-0"><i class="bi bi-bar-chart-line text-primary"></i> Analyse de Performance 2026</h4>
            <span class="badge bg-light text-dark border">Mensuel</span>
        </div>
        <canvas id="mainChart" height="120"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('mainChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mars 2026'],
            datasets: [
                { label: 'Produits (Ventes)', data: [<?= $ca ?>], backgroundColor: '#00264d', borderRadius: 12 },
                { label: 'Charges (Dépenses)', data: [<?= $charges ?>], backgroundColor: '#ff6b6b', borderRadius: 12 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { weight: 'bold' } } } },
            scales: { 
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
</body>
</html>
