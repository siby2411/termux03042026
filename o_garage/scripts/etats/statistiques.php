<?php include '../../includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card shadow border-0 p-4">
            <h5 class="fw-bold"><i class="fas fa-chart-line text-primary"></i> Évolution du Chiffre d'Affaires (Global)</h5>
            <canvas id="revenueChart" height="150"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow border-0 p-4">
            <h5 class="fw-bold">Répartition Ventes</h5>
            <canvas id="pieChart"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Féb', 'Mar', 'Avr', 'Mai'],
        datasets: [{
            label: 'Chiffre d\'Affaires (FCFA)',
            data: [450000, 780000, 1200000, 950000, 1500000],
            borderColor: '#0d47a1',
            backgroundColor: 'rgba(13, 71, 161, 0.1)',
            fill: true,
            tension: 0.4
        }]
    }
});

const ctx2 = document.getElementById('pieChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Main d\'oeuvre', 'Pièces'],
        datasets: [{
            data: [60, 40],
            backgroundColor: ['#ff6d00', '#0d47a1']
        }]
    }
});
</script>
<?php include '../../includes/footer.php'; ?>
