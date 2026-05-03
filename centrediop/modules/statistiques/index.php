<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$periode = $_GET['periode'] ?? 'mois';
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');

try {
    // Statistiques globales
    $global_stats = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT c.id) as total_consultations,
            COUNT(DISTINCT c.patient_id) as total_patients,
            COUNT(DISTINCT r.id) as total_rdv,
            COALESCE(SUM(p.montant_total), 0) as total_recettes
        FROM consultations c
        LEFT JOIN paiements p ON c.id = p.consultation_id
        LEFT JOIN rendez_vous r ON r.date_rdv BETWEEN ? AND ?
        WHERE DATE(c.date_consultation) BETWEEN ? AND ?
    ");
    $global_stats->execute([$date_debut, $date_fin, $date_debut, $date_fin]);
    $global = $global_stats->fetch();

    // Statistiques par service
    $services_stats = $pdo->prepare("
        SELECT 
            s.name,
            s.couleur,
            COUNT(c.id) as consultations
        FROM services s
        LEFT JOIN consultations c ON s.id = c.service_id AND DATE(c.date_consultation) BETWEEN ? AND ?
        GROUP BY s.id, s.name, s.couleur
        ORDER BY consultations DESC
    ");
    $services_stats->execute([$date_debut, $date_fin]);
    $services_stats = $services_stats->fetchAll();

    // Top médecins
    $top_medecins = $pdo->prepare("
        SELECT 
            u.prenom, u.nom,
            COUNT(c.id) as consultations
        FROM users u
        LEFT JOIN consultations c ON u.id = c.medecin_id AND DATE(c.date_consultation) BETWEEN ? AND ?
        WHERE u.role = 'medecin'
        GROUP BY u.id, u.prenom, u.nom
        ORDER BY consultations DESC
        LIMIT 10
    ");
    $top_medecins->execute([$date_debut, $date_fin]);
    $top_medecins = $top_medecins->fetchAll();

    // Évolution journalière
    $daily_stats = $pdo->prepare("
        SELECT 
            DATE(date_consultation) as jour,
            COUNT(*) as consultations
        FROM consultations
        WHERE DATE(date_consultation) BETWEEN ? AND ?
        GROUP BY DATE(date_consultation)
        ORDER BY jour
    ");
    $daily_stats->execute([$date_debut, $date_fin]);
    $daily = $daily_stats->fetchAll();

} catch (Exception $e) {
    $global = ['total_consultations' => 0, 'total_patients' => 0, 'total_rdv' => 0, 'total_recettes' => 0];
    $services_stats = [];
    $top_medecins = [];
    $daily = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques</title>
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
                        <small>Statistiques</small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="../admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="index.php" class="active"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-chart-pie"></i> Statistiques</h2>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-3">
                                <select name="periode" class="form-control">
                                    <option value="mois" <?= $periode == 'mois' ? 'selected' : '' ?>>Ce mois</option>
                                    <option value="semaine" <?= $periode == 'semaine' ? 'selected' : '' ?>>Cette semaine</option>
                                    <option value="annee" <?= $periode == 'annee' ? 'selected' : '' ?>>Cette année</option>
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
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="kpi-card">
                            <div class="kpi-value"><?= $global['total_consultations'] ?? 0 ?></div>
                            <div class="kpi-label">Consultations</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <div class="kpi-value"><?= $global['total_patients'] ?? 0 ?></div>
                            <div class="kpi-label">Patients uniques</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <div class="kpi-value"><?= number_format($global['total_recettes'] ?? 0, 0, ',', ' ') ?> FCFA</div>
                            <div class="kpi-label">Recettes</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                            <div class="kpi-value"><?= $global['total_rdv'] ?? 0 ?></div>
                            <div class="kpi-label">Rendez-vous</div>
                        </div>
                    </div>
                </div>
                
                <!-- Tableaux -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h5 class="mb-3">Top médecins</h5>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Médecin</th>
                                        <th>Consultations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_medecins as $m): ?>
                                    <tr>
                                        <td>Dr. <?= $m['prenom'] ?> <?= $m['nom'] ?></td>
                                        <td><?= $m['consultations'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h5 class="mb-3">Consultations par service</h5>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Consultations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services_stats as $s): ?>
                                    <tr>
                                        <td><?= $s['name'] ?></td>
                                        <td><?= $s['consultations'] ?></td>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
