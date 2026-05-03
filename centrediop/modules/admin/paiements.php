<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

// Récupérer les paiements avec détails
$paiements = $pdo->query("
    SELECT p.*, 
           pat.prenom as patient_prenom, pat.nom as patient_nom,
           pat.code_patient_unique,
           s.name as service_nom,
           u.prenom as caissier_prenom, u.nom as caissier_nom
    FROM paiements p
    JOIN patients pat ON p.patient_id = pat.id
    LEFT JOIN consultations c ON p.consultation_id = c.id
    LEFT JOIN services s ON c.service_id = s.id
    LEFT JOIN users u ON p.caissier_id = u.id
    ORDER BY p.date_paiement DESC
    LIMIT 100
")->fetchAll();

// Statistiques des paiements
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_paiements,
        SUM(montant_total) as total_recettes,
        AVG(montant_total) as montant_moyen,
        COUNT(DISTINCT patient_id) as patients_distincts,
        SUM(CASE WHEN DATE(date_paiement) = CURDATE() THEN montant_total ELSE 0 END) as recettes_aujourdhui
    FROM paiements
")->fetch();

// Paiements par mode - avec vérification pour éviter les graphiques vides
$modes = $pdo->query("
    SELECT mode_paiement, COUNT(*) as count, SUM(montant_total) as total
    FROM paiements
    GROUP BY mode_paiement
")->fetchAll();

// Si aucun mode de paiement, ajouter des données par défaut
if (empty($modes)) {
    $modes = [
        ['mode_paiement' => 'especes', 'count' => 0, 'total' => 0],
        ['mode_paiement' => 'carte', 'count' => 0, 'total' => 0],
        ['mode_paiement' => 'mobile_money', 'count' => 0, 'total' => 0]
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des paiements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Stabiliser le graphique */
        .chart-container {
            height: 300px;
            width: 100%;
            position: relative;
            background: white;
            border-radius: 10px;
            padding: 10px;
        }
        canvas {
            display: block;
            width: 100% !important;
            height: 100% !important;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Administrateur</small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="patients.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="personnel.php"><i class="fas fa-user-md"></i> Personnel</a></li>
                        <li><a href="services.php"><i class="fas fa-building"></i> Services</a></li>
                        <li><a href="prix_services.php"><i class="fas fa-tag"></i> Prix</a></li>
                        <li><a href="consultations.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="paiements.php" class="active"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <li><a href="../statistiques/index.php"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-credit-card"></i> Gestion des paiements</h2>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="kpi-card">
                            <div class="kpi-value"><?= number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') ?> FCFA</div>
                            <div class="kpi-label">Recettes totales</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <div class="kpi-value"><?= $stats['total_paiements'] ?? 0 ?></div>
                            <div class="kpi-label">Nombre de paiements</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <div class="kpi-value"><?= $stats['patients_distincts'] ?? 0 ?></div>
                            <div class="kpi-label">Patients distincts</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                            <div class="kpi-value"><?= number_format($stats['recettes_aujourdhui'] ?? 0, 0, ',', ' ') ?> FCFA</div>
                            <div class="kpi-label">Aujourd'hui</div>
                        </div>
                    </div>
                </div>
                
                <!-- Graphiques des modes de paiement - Version stabilisée -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-chart-pie"></i> Répartition par mode de paiement</h5>
                            <div class="chart-container">
                                <canvas id="paymentChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-chart-bar"></i> Recettes par mode</h5>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Mode</th>
                                        <th>Nombre</th>
                                        <th>Montant</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = $stats['total_recettes'] ?? 1;
                                    foreach ($modes as $m): 
                                    $pourcentage = $total > 0 ? round(($m['total'] / $total) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $m['mode_paiement'] == 'especes' ? 'success' : 
                                                ($m['mode_paiement'] == 'carte' ? 'info' : 
                                                ($m['mode_paiement'] == 'mobile_money' ? 'warning' : 'secondary')) ?>">
                                                <?= $m['mode_paiement'] ?>
                                            </span>
                                        </td>
                                        <td><?= $m['count'] ?></td>
                                        <td><?= number_format($m['total'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                                        <td><?= $pourcentage ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des paiements récents -->
                <div class="dashboard-card">
                    <h5 class="mb-3"><i class="fas fa-list"></i> Paiements récents</h5>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>N° Facture</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Service</th>
                                    <th>Montant</th>
                                    <th>Mode</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paiements as $p): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= $p['numero_facture'] ?></span></td>
                                    <td><?= date('d/m/Y H:i', strtotime($p['date_paiement'])) ?></td>
                                    <td>
                                        <?= $p['patient_prenom'] ?> <?= $p['patient_nom'] ?><br>
                                        <small class="text-muted"><?= $p['code_patient_unique'] ?></small>
                                    </td>
                                    <td><?= $p['service_nom'] ?? '-' ?></td>
                                    <td><strong><?= number_format($p['montant_total'], 0, ',', ' ') ?> FCFA</strong></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $p['mode_paiement'] == 'especes' ? 'success' : 
                                            ($p['mode_paiement'] == 'carte' ? 'info' : 
                                            ($p['mode_paiement'] == 'mobile_money' ? 'warning' : 'secondary')) ?>">
                                            <?= $p['mode_paiement'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Attendre que le DOM soit complètement chargé
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('paymentChart').getContext('2d');
        
        // Détruire le graphique précédent s'il existe
        if (window.paymentChart) {
            window.paymentChart.destroy();
        }
        
        // Créer le nouveau graphique avec des options stabilisées
        window.paymentChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [<?php foreach ($modes as $m) echo "'" . $m['mode_paiement'] . "',"; ?>],
                datasets: [{
                    data: [<?php foreach ($modes as $m) echo ($m['total'] ?? 0) . ","; ?>],
                    backgroundColor: ['#27ae60', '#3498db', '#f39c12', '#e74c3c', '#9b59b6'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 500 // Animation plus courte
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    }
                }
            }
        });
    });
    
    // Redimensionner le graphique quand la fenêtre change
    window.addEventListener('resize', function() {
        if (window.paymentChart) {
            window.paymentChart.resize();
        }
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
