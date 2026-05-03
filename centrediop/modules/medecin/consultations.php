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

// Récupérer les consultations du médecin
$stmt = $db->prepare("
    SELECT c.*, p.nom, p.prenom, p.code_patient_unique,
           DATE_FORMAT(c.date_consultation, '%d/%m/%Y %H:%i') as date_consult
    FROM consultations c
    JOIN patients p ON c.patient_id = p.id
    WHERE c.medecin_id = ?
    ORDER BY c.date_consultation DESC
");
$stmt->execute([$medecin_id]);
$consultations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mes Consultations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between mb-3">
            <h2>Historique des Consultations</h2>
            <a href="dashboard.php" class="btn btn-secondary">Retour</a>
        </div>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Code</th>
                    <th>Motif</th>
                    <th>Diagnostic</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultations as $c): ?>
                <tr>
                    <td><?= $c['date_consult'] ?></td>
                    <td><?= $c['prenom'] ?> <?= $c['nom'] ?></td>
                    <td><?= $c['code_patient_unique'] ?></td>
                    <td><?= substr($c['motif'] ?? '', 0, 50) ?>...</td>
                    <td><?= substr($c['diagnostic'] ?? '', 0, 50) ?>...</td>
                    <td>
                        <a href="dossier.php?patient_id=<?= $c['patient_id'] ?>" class="btn btn-sm btn-info">Voir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
