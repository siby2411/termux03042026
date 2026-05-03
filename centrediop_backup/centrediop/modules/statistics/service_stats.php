<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$stats = getConsultationStatsByService($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques par service</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Statistiques par service</h1>
    </header>
    <main>
        <canvas id="serviceStatsChart" width="600" height="400"></canvas>
        <script>
            const ctx = document.getElementById('serviceStatsChart').getContext('2d');
            const serviceStatsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($stats, 'service_name')) ?>,
                    datasets: [{
                        label: 'Nombre de consultations',
                        data: <?= json_encode(array_column($stats, 'count')) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </main>
</body>
</html>
