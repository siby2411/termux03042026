<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /index.php');
    exit();
}

$pdo = getPDO();

$periode = $_GET['periode'] ?? 'mois';
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');

// Top services
$top_services = $pdo->prepare("
    SELECT s.name, s.couleur, COUNT(c.id) as total,
           SUM(ca.prix_applique) as recettes
    FROM consultations c
    JOIN services s ON c.service_id = s.id
    LEFT JOIN consultation_actes ca ON c.id = ca.consultation_id
    WHERE DATE(c.date_consultation) BETWEEN ? AND ?
    GROUP BY s.id, s.name, s.couleur
    ORDER BY total DESC
    LIMIT 5
");
$top_services->execute([$date_debut, $date_fin]);
$top_services = $top_services->fetchAll();

// Statistiques par médecin
$stats_medecins = $pdo->prepare("
    SELECT u.prenom, u.nom, u.role,
           COUNT(c.id) as nb_consultations,
           SUM(TIMESTAMPDIFF(MINUTE, c.date_consultation, NOW())) as temps_total,
           AVG(TIMESTAMPDIFF(MINUTE, c.date_consultation, NOW())) as duree_moyenne
    FROM consultations c
    JOIN users u ON c.medecin_id = u.id
    WHERE DATE(c.date_consultation) BETWEEN ? AND ?
    GROUP BY u.id, u.prenom, u.nom, u.role
    ORDER BY nb_consultations DESC
");
$stats_medecins->execute([$date_debut, $date_fin]);
$stats_medecins = $stats_medecins->fetchAll();

// Statistiques globales
$global = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT c.id) as total_consultations,
        COUNT(DISTINCT c.patient_id) as total_patients,
        COUNT(DISTINCT f.id) as total_attente,
        SUM(ca.prix_applique) as total_recettes
    FROM consultations c
    LEFT JOIN consultation_actes ca ON c.id = ca.consultation_id
    LEFT JOIN file_attente f ON f.statut = 'en_attente'
    WHERE DATE(c.date_consultation) BETWEEN ? AND ?
");
$global->execute([$date_debut, $date_fin]);
$global = $global->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">🏥 Centre Mamadou Diop - Statistiques</span>
            <span class="text-white"><?= $_SESSION['user_name'] ?></span>
        </div>
    </nav>
    
    <div class="container-fluid mt-3">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="patients.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users"></i> Patients
                    </a>
                    <a href="statistiques.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-chart-line"></i> Statistiques
                    </a>
                    <a href="personnel.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-md"></i> Personnel
                    </a>
                    <a href="/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
            
            <!-- Main content -->
            <div class="col-md-10">
                <h2 class="mb-4">Statistiques</h2>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <select name="periode" class="form-control">
                                    <option value="mois" <?= $periode == 'mois' ? 'selected' : '' ?>>Ce mois</option>
                                    <option value="semaine" <?= $periode == 'semaine' ? 'selected' : '' ?>>Cette semaine</option>
                                    <option value="jour" <?= $periode == 'jour' ? 'selected' : '' ?>>Aujourd'hui</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_debut" class="form-control" value="<?= $date_debut ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_fin" class="form-control" value="<?= $date_fin ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- KPIs -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h6>Consultations</h6>
                                <h3><?= $global['total_consultations'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h6>Patients</h6>
                                <h3><?= $global['total_patients'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <h6>En attente</h6>
                                <h3><?= $global['total_attente'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h6>Recettes</h6>
                                <h3><?= number_format($global['total_recettes'] ?? 0, 0, ',', ' ') ?> FCFA</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top services -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Top 5 services les plus sollicités</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="servicesChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Performance des médecins</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Médecin</th>
                                            <th>Consultations</th>
                                            <th>Durée moy.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats_medecins as $m): ?>
                                        <tr>
                                            <td>Dr. <?= $m['prenom'] ?> <?= $m['nom'] ?></td>
                                            <td><?= $m['nb_consultations'] ?></td>
                                            <td><?= round($m['duree_moyenne']) ?> min</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    const ctx = document.getElementById('servicesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [<?php foreach ($top_services as $s) echo "'" . $s['name'] . "',"; ?>],
            datasets: [{
                label: 'Nombre de consultations',
                data: [<?php foreach ($top_services as $s) echo $s['total'] . ","; ?>],
                backgroundColor: [<?php foreach ($top_services as $s) echo "'" . ($s['couleur'] ?? '#3498db') . "',"; ?>]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    </script>
</body>
</html>
