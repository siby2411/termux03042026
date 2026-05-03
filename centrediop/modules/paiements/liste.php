<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'caissier'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$paiements = $pdo->query("
    SELECT p.*, pat.prenom, pat.nom, pat.code_patient_unique
    FROM paiements p
    JOIN patients pat ON p.patient_id = pat.id
    ORDER BY p.date_paiement DESC
    LIMIT 50
")->fetchAll();

$total = $pdo->query("SELECT SUM(montant_total) FROM paiements")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des paiements</title>
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
                        <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="../consultation/form.php"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <li><a href="liste.php" class="active"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="form.php"><i class="fas fa-plus-circle"></i> Nouveau paiement</a></li>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <?php endif; ?>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-credit-card"></i> Liste des paiements</h2>
                    <div class="badge bg-success p-3">Total: <?= number_format($total, 0, ',', ' ') ?> FCFA</div>
                </div>
                
                <div class="dashboard-card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N° Facture</th>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Montant</th>
                                <th>Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paiements as $p): ?>
                            <tr>
                                <td><?= $p['numero_facture'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($p['date_paiement'])) ?></td>
                                <td><?= $p['prenom'] ?> <?= $p['nom'] ?><br><small><?= $p['code_patient_unique'] ?></small></td>
                                <td><?= number_format($p['montant_total'], 0, ',', ' ') ?> FCFA</td>
                                <td><?= $p['mode_paiement'] ?></td>
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
