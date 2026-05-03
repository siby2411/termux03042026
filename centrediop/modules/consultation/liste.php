<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$consultations = $pdo->query("
    SELECT c.*, p.prenom, p.nom, p.code_patient_unique,
           u.prenom as medecin_prenom, u.nom as medecin_nom,
           s.name as service_nom
    FROM consultations c
    JOIN patients p ON c.patient_id = p.id
    JOIN users u ON c.medecin_id = u.id
    JOIN services s ON c.service_id = s.id
    ORDER BY c.date_consultation DESC
    LIMIT 50
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des consultations</title>
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
                        <small><?= ucfirst($_SESSION['user_role']) ?></small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="../<?= $_SESSION['user_role'] ?>/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="../patients/liste.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="../patients/form.php"><i class="fas fa-user-plus"></i> Nouveau patient</a></li>
                        <li><a href="liste.php" class="active"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="form.php"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <?php if ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'caissier'): ?>
                        <li><a href="../paiements/liste.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="../paiements/form.php"><i class="fas fa-plus-circle"></i> Nouveau paiement</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <?php endif; ?>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-list"></i> Liste des consultations</h2>
                
                <div class="dashboard-card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Médecin</th>
                                <th>Service</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consultations as $c): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($c['date_consultation'])) ?></td>
                                <td><?= $c['prenom'] ?> <?= $c['nom'] ?><br><small><?= $c['code_patient_unique'] ?></small></td>
                                <td>Dr. <?= $c['medecin_prenom'] ?> <?= $c['medecin_nom'] ?></td>
                                <td><?= $c['service_nom'] ?></td>
                                <td>
                                    <a href="../paiements/form.php?consultation_id=<?= $c['id'] ?>" class="btn btn-sm btn-success">Paiement</a>
                                </td>
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
