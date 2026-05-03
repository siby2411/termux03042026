<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'caissier') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$periode = $_GET['periode'] ?? 'jour';
$date = $_GET['date'] ?? date('Y-m-d');

// Statistiques par département
$stats = $pdo->query("
    SELECT 
        s.name as departement,
        s.prix_consultation,
        COUNT(DISTINCT c.id) as nb_consultations,
        COUNT(DISTINCT p.id) as nb_paiements,
        COALESCE(SUM(p.montant_total), 0) as total_recettes,
        COUNT(DISTINCT f.id) as en_attente,
        AVG(ca.prix_applique) as prix_moyen
    FROM services s
    LEFT JOIN consultations c ON s.id = c.service_id AND DATE(c.date_consultation) = CURDATE()
    LEFT JOIN consultation_actes ca ON c.id = ca.consultation_id
    LEFT JOIN paiements p ON c.id = p.consultation_id AND DATE(p.date_paiement) = CURDATE()
    LEFT JOIN file_attente f ON s.id = f.service_id AND f.statut = 'en_attente'
    WHERE s.name NOT IN ('Caisse', 'Accueil/Triage')
    GROUP BY s.id
    ORDER BY nb_consultations DESC
")->fetchAll();

$total_recettes = array_sum(array_column($stats, 'total_recettes'));
$total_consultations = array_sum(array_column($stats, 'nb_consultations'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques par département</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Caissier</small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="etat_caisse.php"><i class="fas fa-chart-line"></i> État de caisse</a></li>
                        <li><a href="liste_patients.php"><i class="fas fa-users"></i> Liste patients</a></li>
                        <li><a href="stats_departements.php" class="active"><i class="fas fa-chart-pie"></i> Stats départements</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-chart-pie"></i> Statistiques par département</h2>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="kpi-card">
                            <div class="kpi-value"><?= $total_consultations ?></div>
                            <div class="kpi-label">Consultations aujourd'hui</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <div class="kpi-value"><?= number_format($total_recettes, 0, ',', ' ') ?> FCFA</div>
                            <div class="kpi-label">Recettes totales</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-table"></i> Détail par département</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Département</th>
                                        <th>Prix consultation</th>
                                        <th>Consultations</th>
                                        <th>Paiements</th>
                                        <th>Recettes</th>
                                        <th>En attente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats as $s): ?>
                                    <tr>
                                        <td><strong><?= $s['departement'] ?></strong></td>
                                        <td><?= number_format($s['prix_consultation'], 0, ',', ' ') ?> FCFA</td>
                                        <td><?= $s['nb_consultations'] ?></td>
                                        <td><?= $s['nb_paiements'] ?></td>
                                        <td><?= number_format($s['total_recettes'], 0, ',', ' ') ?> FCFA</td>
                                        <td><?= $s['en_attente'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-chart-pie"></i> Répartition des consultations</h5>
                            <canvas id="deptChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    const ctx = document.getElementById('deptChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [<?php foreach ($stats as $s) echo "'" . $s['departement'] . "',"; ?>],
            datasets: [{
                data: [<?php foreach ($stats as $s) echo $s['nb_consultations'] . ","; ?>],
                backgroundColor: [
                    '#3498db', '#2ecc71', '#e74c3c', '#f39c12', 
                    '#9b59b6', '#1abc9c', '#e67e22', '#34495e'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
