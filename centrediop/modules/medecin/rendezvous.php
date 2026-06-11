<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$medecin_id = $_SESSION['user_id'];

// Récupérer tous les rendez-vous du médecin
$stmt = $db->prepare("
    SELECT r.*, p.nom, p.prenom, p.code_patient_unique, p.telephone,
           s.name as service_nom,
           DATE_FORMAT(r.date_rdv, '%d/%m/%Y') as date_rdv_format
    FROM rendez_vous r
    JOIN patients p ON r.patient_id = p.id
    JOIN services s ON r.service_id = s.id
    WHERE r.medecin_id = ?
    ORDER BY r.date_rdv DESC, r.heure_rdv DESC
");
$stmt->execute([$medecin_id]);
$rdv = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mes Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between mb-3">
            <h2>Gestion des Rendez-vous</h2>
            <a href="dashboard.php" class="btn btn-secondary">Retour</a>
        </div>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Patient</th>
                    <th>Code</th>
                    <th>Téléphone</th>
                    <th>Service</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rdv as $r): ?>
                <tr>
                    <td><?= $r['date_rdv_format'] ?></td>
                    <td><?= substr($r['heure_rdv'], 0, 5) ?></td>
                    <td><?= $r['prenom'] ?> <?= $r['nom'] ?></td>
                    <td><?= $r['code_patient_unique'] ?></td>
                    <td><?= $r['telephone'] ?></td>
                    <td><?= $r['service_nom'] ?></td>
                    <td>
                        <span class="badge bg-<?= $r['statut'] == 'confirme' ? 'success' : ($r['statut'] == 'programme' ? 'warning' : 'secondary') ?>">
                            <?= $r['statut'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="consultation.php?patient_id=<?= $r['patient_id'] ?>" class="btn btn-sm btn-primary">Consulter</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
