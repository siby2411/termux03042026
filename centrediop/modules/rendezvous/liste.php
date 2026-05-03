<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

// Gestion des actions
if (isset($_GET['confirmer'])) {
    $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'confirme' WHERE id = ?");
    $stmt->execute([$_GET['confirmer']]);
    header('Location: liste.php');
    exit();
}

if (isset($_GET['annuler'])) {
    $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE id = ?");
    $stmt->execute([$_GET['annuler']]);
    header('Location: liste.php');
    exit();
}

// Récupérer les rendez-vous selon le rôle
if ($_SESSION['user_role'] == 'admin') {
    $rendezvous = $pdo->query("
        SELECT r.*, 
               p.prenom as patient_prenom, p.nom as patient_nom,
               p.code_patient_unique, p.telephone,
               s.name as service_nom,
               u.prenom as medecin_prenom, u.nom as medecin_nom
        FROM rendez_vous r
        JOIN patients p ON r.patient_id = p.id
        JOIN services s ON r.service_id = s.id
        LEFT JOIN users u ON r.medecin_id = u.id
        ORDER BY r.date_rdv, r.heure_rdv
    ")->fetchAll();
} else {
    $rendezvous = $pdo->prepare("
        SELECT r.*, 
               p.prenom as patient_prenom, p.nom as patient_nom,
               p.code_patient_unique, p.telephone,
               s.name as service_nom
        FROM rendez_vous r
        JOIN patients p ON r.patient_id = p.id
        JOIN services s ON r.service_id = s.id
        WHERE r.medecin_id = ? OR r.service_id = (SELECT service_id FROM users WHERE id = ?)
        ORDER BY r.date_rdv, r.heure_rdv
    ");
    $rendezvous->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $rendezvous = $rendezvous->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des rendez-vous</title>
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
                        <small><?= ucfirst($_SESSION['user_role']) ?></small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="liste.php" class="active"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="form.php"><i class="fas fa-plus-circle"></i> Nouveau rendez-vous</a></li>
                        <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="../paiements/liste.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-calendar-alt"></i> Liste des rendez-vous</h2>
                    <a href="form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau rendez-vous
                    </a>
                </div>
                
                <div class="dashboard-card">
                    <ul class="nav nav-tabs mb-3" id="rdvTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#today">Aujourd'hui</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#upcoming">À venir</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#past">Passés</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <?php
                        $today = date('Y-m-d');
                        $today_rdv = array_filter($rendezvous, fn($r) => $r['date_rdv'] == $today);
                        $upcoming_rdv = array_filter($rendezvous, fn($r) => $r['date_rdv'] > $today);
                        $past_rdv = array_filter($rendezvous, fn($r) => $r['date_rdv'] < $today);
                        ?>
                        
                        <div class="tab-pane active" id="today">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Heure</th>
                                        <th>Patient</th>
                                        <th>Service</th>
                                        <th>Médecin</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_rdv as $r): ?>
                                    <tr>
                                        <td><?= substr($r['heure_rdv'], 0, 5) ?></td>
                                        <td>
                                            <?= $r['patient_prenom'] ?> <?= $r['patient_nom'] ?><br>
                                            <small><?= $r['code_patient_unique'] ?></small>
                                        </td>
                                        <td><?= $r['service_nom'] ?></td>
                                        <td><?= $r['medecin_prenom'] ?? 'N/A' ?> <?= $r['medecin_nom'] ?? '' ?></td>
                                        <td><?= $r['motif'] ?></td>
                                        <td>
                                            <span class="badge <?= 
                                                $r['statut'] == 'programme' ? 'bg-warning' : 
                                                ($r['statut'] == 'confirme' ? 'bg-info' : 
                                                ($r['statut'] == 'honore' ? 'bg-success' : 'bg-danger')) ?>">
                                                <?= $r['statut'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($r['statut'] == 'programme'): ?>
                                            <a href="?confirmer=<?= $r['id'] ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="?annuler=<?= $r['id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Annuler ce rendez-vous ?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="../consultation/form.php?patient_id=<?= $r['patient_id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-stethoscope"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="tab-pane" id="upcoming">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Patient</th>
                                        <th>Service</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming_rdv as $r): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($r['date_rdv'])) ?></td>
                                        <td><?= substr($r['heure_rdv'], 0, 5) ?></td>
                                        <td><?= $r['patient_prenom'] ?> <?= $r['patient_nom'] ?></td>
                                        <td><?= $r['service_nom'] ?></td>
                                        <td>
                                            <span class="badge <?= $r['statut'] == 'programme' ? 'bg-warning' : 'bg-info' ?>">
                                                <?= $r['statut'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="tab-pane" id="past">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Service</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($past_rdv as $r): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($r['date_rdv'])) ?></td>
                                        <td><?= $r['patient_prenom'] ?> <?= $r['patient_nom'] ?></td>
                                        <td><?= $r['service_nom'] ?></td>
                                        <td>
                                            <span class="badge <?= $r['statut'] == 'honore' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $r['statut'] ?>
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
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
