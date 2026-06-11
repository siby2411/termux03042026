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

// Récupérer tous les patients qui ont eu un rendez-vous avec ce médecin
$stmt = $db->prepare("
    SELECT DISTINCT p.*, 
           MAX(r.date_rdv) as dernier_rdv,
           COUNT(r.id) as nb_rdv
    FROM patients p
    JOIN rendez_vous r ON p.id = r.patient_id
    WHERE r.medecin_id = ?
    GROUP BY p.id
    ORDER BY dernier_rdv DESC
");
$stmt->execute([$medecin_id]);
$patients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mes Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between mb-3">
            <h2>Mes Patients</h2>
            <a href="dashboard.php" class="btn btn-secondary">Retour</a>
        </div>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Téléphone</th>
                    <th>Dernier RDV</th>
                    <th>Nb RDV</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $p): ?>
                <tr>
                    <td><?= $p['code_patient_unique'] ?></td>
                    <td><?= $p['nom'] ?></td>
                    <td><?= $p['prenom'] ?></td>
                    <td><?= $p['telephone'] ?></td>
                    <td><?= $p['dernier_rdv'] ?></td>
                    <td><?= $p['nb_rdv'] ?></td>
                    <td>
                        <a href="consultation.php?patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Consulter</a>
                        <a href="dossier.php?patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-info">Dossier</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
