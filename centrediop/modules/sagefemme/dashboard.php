<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'sagefemme') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$service_id = $_SESSION['user_service'] ?? 5; // Gynécologie par défaut

// File d'attente en temps réel
$queue = $pdo->prepare("
    SELECT f.*, p.prenom, p.nom, p.code_patient_unique, p.date_naissance,
           TIMESTAMPDIFF(YEAR, p.date_naissance, CURDATE()) as age
    FROM file_attente f
    JOIN patients p ON f.patient_id = p.id
    WHERE f.service_id = ? AND f.statut = 'en_attente'
    ORDER BY FIELD(f.priorite, 'urgence', 'senior', 'normal'), f.cree_a ASC
");
$queue->execute([$service_id]);
$waiting = $queue->fetchAll();

// Consultations du jour
$consultations = $pdo->prepare("
    SELECT c.*, p.prenom, p.nom, p.code_patient_unique
    FROM consultations c
    JOIN patients p ON c.patient_id = p.id
    WHERE c.service_id = ? AND DATE(c.date_consultation) = CURDATE()
    ORDER BY c.date_consultation DESC
");
$consultations->execute([$service_id]);
$today_consults = $consultations->fetchAll();

// Rendez-vous futurs
$futurs_rdv = $pdo->prepare("
    SELECT r.*, p.prenom, p.nom, p.telephone, p.code_patient_unique
    FROM rendez_vous r
    JOIN patients p ON r.patient_id = p.id
    WHERE r.service_id = ? AND r.date_rdv >= CURDATE()
    ORDER BY r.date_rdv, r.heure_rdv
    LIMIT 10
");
$futurs_rdv->execute([$service_id]);
$upcoming_rdv = $futurs_rdv->fetchAll();

// Recherche de rendez-vous
$search_results = null;
if (isset($_GET['search_rdv'])) {
    $search = '%' . $_GET['search_rdv'] . '%';
    $stmt = $pdo->prepare("
        SELECT r.*, p.prenom, p.nom, p.telephone, p.code_patient_unique
        FROM rendez_vous r
        JOIN patients p ON r.patient_id = p.id
        WHERE r.service_id = ? AND (p.prenom LIKE ? OR p.nom LIKE ? OR p.code_patient_unique LIKE ?)
        ORDER BY r.date_rdv DESC
        LIMIT 20
    ");
    $stmt->execute([$service_id, $search, $search, $search]);
    $search_results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sage-femme</title>
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
                        <small>Sage-femme</small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="/modules/medical/edition_dossier.php"><i class="fas fa-edit"></i> Édition dossier</a></li>
                        <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="../patients/liste.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="../patients/form.php"><i class="fas fa-user-plus"></i> Nouveau patient</a></li>
                        <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="../consultation/form.php"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-female"></i> Dashboard Sage-femme</h2>
                
                <div class="row">
                    <!-- File d'attente -->
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-clock"></i> File d'attente (<?= count($waiting) ?>)</h5>
                            <div style="max-height: 500px; overflow-y: auto;">
                                <?php if (empty($waiting)): ?>
                                    <p class="text-muted">Aucun patient en attente</p>
                                <?php else: ?>
                                    <?php foreach ($waiting as $w): ?>
                                    <div class="patient-card <?= $w['priorite'] ?>">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?= $w['prenom'] ?> <?= $w['nom'] ?></strong>
                                                <span class="badge <?= $w['priorite'] == 'senior' ? 'bg-warning' : 'bg-info' ?>">
                                                    <?= $w['priorite'] ?>
                                                </span>
                                            </div>
                                            <small><?= $w['age'] ?> ans</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small>Token: <?= $w['token'] ?> - <?= date('H:i', strtotime($w['cree_a'])) ?></small>
                                            <a href="../consultation/form.php?patient_id=<?= $w['patient_id'] ?>&file_id=<?= $w['id'] ?>" 
                                               class="btn btn-sm btn-primary">Consulter</a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Consultations du jour -->
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-stethoscope"></i> Consultations du jour (<?= count($today_consults) ?>)</h5>
                            <div style="max-height: 500px; overflow-y: auto;">
                                <?php if (empty($today_consults)): ?>
                                    <p class="text-muted">Aucune consultation aujourd'hui</p>
                                <?php else: ?>
                                    <?php foreach ($today_consults as $c): ?>
                                    <div class="patient-card">
                                        <strong><?= $c['prenom'] ?> <?= $c['nom'] ?></strong><br>
                                        <small><?= $c['code_patient_unique'] ?> - <?= date('H:i', strtotime($c['date_consultation'])) ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rendez-vous futurs -->
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-calendar"></i> Rendez-vous futurs</h5>
                            
                            <!-- Recherche -->
                            <form method="GET" class="mb-3">
                                <div class="input-group">
                                    <input type="text" name="search_rdv" class="form-control" placeholder="Rechercher un patient...">
                                    <button type="submit" class="btn btn-info"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                            
                            <?php if ($search_results !== null): ?>
                                <h6 class="mt-2">Résultats:</h6>
                                <?php foreach ($search_results as $r): ?>
                                <div class="patient-card">
                                    <strong><?= $r['prenom'] ?> <?= $r['nom'] ?></strong><br>
                                    <small><?= date('d/m/Y', strtotime($r['date_rdv'])) ?></small>
                                </div>
                                <?php endforeach; ?>
                                <hr>
                            <?php endif; ?>
                            
                            <div style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($upcoming_rdv as $r): ?>
                                <div class="patient-card">
                                    <strong><?= $r['prenom'] ?> <?= $r['nom'] ?></strong><br>
                                    <small>
                                        <?= date('d/m/Y', strtotime($r['date_rdv'])) ?> à <?= substr($r['heure_rdv'], 0, 5) ?>
                                    </small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
