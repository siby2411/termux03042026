<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

// Récupérer les consultations du médecin
$consultations = $pdo->prepare("
    SELECT c.*, p.prenom, p.nom, p.code_patient_unique,
           s.name as service_nom
    FROM consultations c
    JOIN patients p ON c.patient_id = p.id
    JOIN services s ON c.service_id = s.id
    WHERE c.medecin_id = ? AND DATE(c.date_consultation) = CURDATE()
    ORDER BY c.date_consultation DESC
");
$consultations->execute([$_SESSION['user_id']]);
$today_consultations = $consultations->fetchAll();

// Rendez-vous du jour
$rdv = $pdo->prepare("
    SELECT r.*, p.prenom, p.nom, p.code_patient_unique, p.telephone
    FROM rendez_vous r
    JOIN patients p ON r.patient_id = p.id
    WHERE r.medecin_id = ? AND r.date_rdv = CURDATE()
    ORDER BY r.heure_rdv
");
$rdv->execute([$_SESSION['user_id']]);
$today_rdv = $rdv->fetchAll();

// Statistiques
$stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_consultations,
        COUNT(DISTINCT patient_id) as total_patients
    FROM consultations
    WHERE medecin_id = ? AND DATE(date_consultation) = CURDATE()
");
$stats->execute([$_SESSION['user_id']]);
$daily_stats = $stats->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Médecin</title>
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
                        <small>Dr. <?= $_SESSION['user_name'] ?></small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="dashboard_complet.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="../consultation/form.php"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-stethoscope"></i> Dashboard Médecin</h2>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="kpi-card">
                            <div class="kpi-value"><?= $daily_stats['total_consultations'] ?? 0 ?></div>
                            <div class="kpi-label">Consultations aujourd'hui</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <div class="kpi-value"><?= $daily_stats['total_patients'] ?? 0 ?></div>
                            <div class="kpi-label">Patients aujourd'hui</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-clock"></i> Consultations du jour</h5>
                            <?php if (empty($today_consultations)): ?>
                                <p class="text-muted">Aucune consultation aujourd'hui</p>
                            <?php else: ?>
                                <?php foreach ($today_consultations as $c): ?>
                                <div class="queue-item">
                                    <div><?= $c['prenom'] ?> <?= $c['nom'] ?> (<?= $c['code_patient_unique'] ?>)</div>
                                    <small><?= date('H:i', strtotime($c['date_consultation'])) ?> - <?= $c['service_nom'] ?></small>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-calendar"></i> Rendez-vous du jour</h5>
                            <?php if (empty($today_rdv)): ?>
                                <p class="text-muted">Aucun rendez-vous aujourd'hui</p>
                            <?php else: ?>
                                <?php foreach ($today_rdv as $r): ?>
                                <div class="queue-item">
                                    <div><?= $r['prenom'] ?> <?= $r['nom'] ?></div>
                                    <small><?= substr($r['heure_rdv'], 0, 5) ?> - <?= $r['telephone'] ?></small>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="../consultation/form.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nouvelle consultation
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
