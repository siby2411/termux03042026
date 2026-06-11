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
<?php include $_SERVER["DOCUMENT_ROOT"] . "/includes/sidebar.php"; ?>
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
