<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$period = $_GET['period'] ?? 'week';

$stats = $pdo->query("
    SELECT 
        u.prenom, u.nom, u.role,
        COUNT(p.id) as jours_present,
        AVG(TIMESTAMPDIFF(HOUR, p.heure_arrivee, p.heure_depart)) as heures_moyennes,
        SUM(TIMESTAMPDIFF(HOUR, p.heure_arrivee, p.heure_depart)) as heures_total
    FROM users u
    LEFT JOIN pointages p ON u.id = p.user_id
    WHERE p.date_pointage >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY u.id
    ORDER BY heures_total DESC
")->fetchAll();

$total_heures = array_sum(array_column($stats, 'heures_total'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques de pointage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="../admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="index.php"><i class="fas fa-fingerprint"></i> Pointage</a></li>
                        <li><a href="stats.php" class="active"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-chart-line"></i> Statistiques de pointage</h2>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="kpi-card">
                            <div class="kpi-value"><?= count($stats) ?></div>
                            <div class="kpi-label">Personnel actif</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <div class="kpi-value"><?= round($total_heures) ?>h</div>
                            <div class="kpi-label">Heures totales</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <div class="kpi-value"><?= round($total_heures / count($stats)) ?>h</div>
                            <div class="kpi-label">Moyenne/personne</div>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Personnel</th>
                                <th>Rôle</th>
                                <th>Jours présents</th>
                                <th>Heures totales</th>
                                <th>Moyenne/jour</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats as $s): ?>
                            <tr>
                                <td><?= $s['prenom'] ?> <?= $s['nom'] ?></td>
                                <td><?= $s['role'] ?></td>
                                <td><?= $s['jours_present'] ?></td>
                                <td><?= round($s['heures_total']) ?>h</td>
                                <td><?= round($s['heures_moyennes']) ?>h</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
