<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$date = $_GET['date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("SELECT p.*, u.first_name, u.last_name, u.role
                       FROM presences p
                       JOIN users u ON p.personnel_id = u.id
                       WHERE p.date = ?
                       ORDER BY u.last_name, u.first_name");
$stmt->execute([$date]);
$presences = $stmt->fetchAll();

$personnel = $pdo->query("SELECT id, first_name, last_name, role FROM users WHERE role IN ('biologiste','technicien','secretaire') ORDER BY last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Présences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Présences du personnel</h2>
        <form method="get" class="row g-3 mb-4">
            <div class="col-auto"><label>Date :</label></div>
            <div class="col-auto"><input type="date" name="date" value="<?= $date ?>" class="form-control"></div>
            <div class="col-auto"><button type="submit" class="btn btn-primary">Voir</button></div>
            <div class="col-auto"><a href="ajouter.php" class="btn btn-success">Ajouter présence</a></div>
        </form>

        <table class="table table-striped">
            <thead><tr><th>Personnel</th><th>Rôle</th><th>Heure arrivée</th><th>Heure départ</th><th>Présent</th><th>Actions</th> </thead>
            <tbody>
                <?php foreach ($presences as $p): ?>
                <tr>
                    <td><?= escape($p['last_name'] . ' ' . $p['first_name']) ?></td>
                    <td><?= escape($p['role']) ?></td>
                    <td><?= $p['heure_arrivee'] ?></td>
                    <td><?= $p['heure_depart'] ?></td>
                    <td><?= $p['present'] ? 'Oui' : 'Non' ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
