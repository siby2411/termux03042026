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

// Récupérer le planning du médecin
$stmt = $db->prepare("
    SELECT ms.*, s.numero_salle, s.etage, b.nom as batiment
    FROM medecin_salles ms
    JOIN salles s ON ms.salle_id = s.id
    JOIN batiments b ON s.batiment_id = b.id
    WHERE ms.medecin_id = ?
    ORDER BY ms.date_affectation DESC, ms.heure_debut
");
$stmt->execute([$medecin_id]);
$planning = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mon Planning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between mb-3">
            <h2>Mon Planning</h2>
            <a href="dashboard.php" class="btn btn-secondary">Retour</a>
        </div>
        
        <?php if (empty($planning)): ?>
            <div class="alert alert-info">Aucun planning trouvé</div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Salle</th>
                        <th>Étage</th>
                        <th>Bâtiment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($planning as $p): ?>
                    <tr>
                        <td><?= $p['date_affectation'] ?></td>
                        <td><?= substr($p['heure_debut'], 0, 5) ?> - <?= substr($p['heure_fin'], 0, 5) ?></td>
                        <td>Salle <?= $p['numero_salle'] ?></td>
                        <td><?= $p['etage'] ?: 'RDC' ?></td>
                        <td><?= $p['batiment'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
