<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

// Récupérer les consultations avec détails
$consultations = $pdo->query("
    SELECT c.*, 
           p.prenom as patient_prenom, p.nom as patient_nom,
           p.code_patient_unique,
           u.prenom as medecin_prenom, u.nom as medecin_nom,
           s.name as service_nom
    FROM consultations c
    JOIN patients p ON c.patient_id = p.id
    JOIN users u ON c.medecin_id = u.id
    JOIN services s ON c.service_id = s.id
    ORDER BY c.date_consultation DESC
    LIMIT 100
")->fetchAll();

// Statistiques des consultations
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        COUNT(DISTINCT patient_id) as patients_uniques,
        COUNT(DISTINCT medecin_id) as medecins_actifs,
        SUM(CASE WHEN DATE(date_consultation) = CURDATE() THEN 1 ELSE 0 END) as aujourdhui,
        AVG(TIMESTAMPDIFF(HOUR, date_consultation, NOW())) as duree_moyenne
    FROM consultations
")->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
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
                        <li><a href="consultations.php" class="active"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="paiements.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <li><a href="../statistiques/index.php"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-stethoscope"></i> Consultations</h2>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="kpi-card">
                            <div class="kpi-value"><?= $stats['total'] ?></div>
                            <div class="kpi-label">Total consultations</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <div class="kpi-value"><?= $stats['patients_uniques'] ?></div>
                            <div class="kpi-label">Patients uniques</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <div class="kpi-value"><?= $stats['medecins_actifs'] ?></div>
                            <div class="kpi-label">Médecins actifs</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                            <div class="kpi-value"><?= $stats['aujourdhui'] ?></div>
                            <div class="kpi-label">Aujourd'hui</div>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des consultations -->
                <div class="dashboard-card">
                    <h5 class="mb-3"><i class="fas fa-list"></i> Consultations récentes</h5>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>N° Consultation</th>
                                <th>Date/Heure</th>
                                <th>Patient</th>
                                <th>Médecin</th>
                                <th>Service</th>
                                <th>Motif</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consultations as $c): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $c['numero_consultation'] ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($c['date_consultation'])) ?></td>
                                <td>
                                    <?= $c['patient_prenom'] ?> <?= $c['patient_nom'] ?><br>
                                    <small class="text-muted"><?= $c['code_patient_unique'] ?></small>
                                </td>
                                <td>Dr. <?= $c['medecin_prenom'] ?> <?= $c['medecin_nom'] ?></td>
                                <td><?= $c['service_nom'] ?></td>
                                <td><?= substr($c['motif_consultation'] ?? '', 0, 50) ?></td>
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
