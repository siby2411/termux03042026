<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$doctorStats = getTreatmentStatsByDoctor($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques par médecin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Statistiques par médecin</h1>
    </header>
    <main>
        <canvas id="doctorStatsChart" width="600" height="400"></canvas>
        <script>
            const ctx = document.getElementById('doctorStatsChart').getContext('2d');
            const doctorStatsChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_column($doctorStats, 'doctor_name')) ?>,
                    datasets: [{
                        label: 'Nombre de traitements',
                        data: <?= json_encode(array_column($doctorStats, 'count')) ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                }
            });
        </script>
    </main>
</body>
</html>
