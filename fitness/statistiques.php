<?php
require_once 'config/database.php';
include 'header.php';
$database = new Database();
$db = $database->getConnection();

// Statistiques par discipline
$query = "SELECT discipline_principale, COUNT(*) as total FROM adherents GROUP BY discipline_principale";
$stats_discipline = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Statistiques Globales</h3>
        </div>
        <div class="card-body">
            <canvas id="disciplineChart" height="100"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('disciplineChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($stats_discipline, 'discipline_principale')) ?>,
        datasets: [{
            label: 'Nombre d\'adhérents',
            data: <?= json_encode(array_column($stats_discipline, 'total')) ?>,
            backgroundColor: 'rgba(255,75,43,0.6)'
        }]
    }
});
</script>

<?php include 'footer.php'; ?>
