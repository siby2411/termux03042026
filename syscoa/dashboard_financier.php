<?php
session_start();
require_once 'config/database.php';

$title = "Tableau de Bord Financier";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - SYSCOHADA Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i><?php echo $title; ?>
                </h1>
                <div class="btn-group">
                    <button class="btn btn-outline-primary active">Mensuel</button>
                    <button class="btn btn-outline-primary">Trimestriel</button>
                    <button class="btn btn-outline-primary">Annuel</button>
                </div>
            </div>

            <!-- KPI Financiers -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="dashboard-card stat-card primary">
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h3>2.5M</h3>
                        <p>Chiffre d'Affaires</p>
                        <small class="text-success"><i class="fas fa-arrow-up"></i> +15% vs N-1</small>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="dashboard-card stat-card success">
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>18.5%</h3>
                        <p>Rentabilité Nette</p>
                        <small class="text-success"><i class="fas fa-arrow-up"></i> +3.2%</small>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="dashboard-card stat-card warning">
                        <div class="icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h3>1.8</h3>
                        <p>Ratio Liquidité</p>
                        <small class="text-warning"><i class="fas fa-minus"></i> Stable</small>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="dashboard-card stat-card danger">
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3>45 j</h3>
                        <p>Délai Clients</p>
                        <small class="text-danger"><i class="fas fa-arrow-up"></i> +5 j</small>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Évolution du Chiffre d'Affaires</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="caChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition des Charges</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chargesChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ratios Financiers -->
            <div class="dashboard-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Indicateurs Clés de Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Indicateur</th>
                                    <th>Valeur</th>
                                    <th>Référence</th>
                                    <th>Tendance</th>
                                    <th>Statut</th>
                                    <th>Évolution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Liquidité Générale</strong></td>
                                    <td>1.85</td>
                                    <td>> 1.5</td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                    <td><span class="badge bg-success">Bon</span></td>
                                    <td class="text-success">+0.15</td>
                                </tr>
                                <tr>
                                    <td><strong>Autonomie Financière</strong></td>
                                    <td>42%</td>
                                    <td>> 30%</td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                    <td><span class="badge bg-success">Bon</span></td>
                                    <td class="text-success">+5%</td>
                                </tr>
                                <tr>
                                    <td><strong>Rentabilité des Capitaux</strong></td>
                                    <td>18.5%</td>
                                    <td>> 12%</td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                    <td><span class="badge bg-success">Excellent</span></td>
                                    <td class="text-success">+3.2%</td>
                                </tr>
                                <tr>
                                    <td><strong>Délai de Paiement Clients</strong></td>
                                    <td>45 jours</td>
                                    <td>< 45 jours</td>
                                    <td><i class="fas fa-arrow-up text-danger"></i></td>
                                    <td><span class="badge bg-warning">À surveiller</span></td>
                                    <td class="text-danger">+5 jours</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Alertes et Recommandations -->
            <div class="row">
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Alertes Financières</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Délai clients en augmentation</strong>
                                <br>
                                <small>Passé de 40 à 45 jours en un mois</small>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Trésorerie excédentaire détectée</strong>
                                <br>
                                <small>Opportunité d'investissement à étudier</small>
                            </div>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Rentabilité en hausse constante</strong>
                                <br>
                                <small>+3.2% par rapport au trimestre dernier</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Recommandations</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Renforcer le recouvrement clients
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Optimiser la gestion des stocks
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Investir l'excédent de trésorerie
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Maintenir la politique de dividendes
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique CA
        const caCtx = document.getElementById('caChart').getContext('2d');
        const caChart = new Chart(caCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: 'Chiffre d\'Affaires 2024',
                    data: [1800000, 1950000, 2100000, 2200000, 2350000, 2500000, 2400000, 2550000, 2650000, 2800000, 2900000, 2500000],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Chiffre d\'Affaires 2023',
                    data: [1500000, 1600000, 1750000, 1850000, 1900000, 2000000, 1950000, 2100000, 2200000, 2300000, 2400000, 2200000],
                    borderColor: '#95a5a6',
                    backgroundColor: 'rgba(149, 165, 166, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Évolution du Chiffre d\'Affaires (FCFA)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });

        // Graphique charges
        const chargesCtx = document.getElementById('chargesChart').getContext('2d');
        const chargesChart = new Chart(chargesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Personnel', 'Achats', 'Impôts', 'Services', 'Autres'],
                datasets: [{
                    data: [45, 25, 15, 10, 5],
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#e74c3c',
                        '#f39c12',
                        '#9b59b6'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
