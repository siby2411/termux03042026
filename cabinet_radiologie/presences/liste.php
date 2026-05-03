<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$date = $_GET['date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT p.*, u.first_name, u.last_name, u.role
    FROM presences p
    JOIN users u ON p.personnel_id = u.id
    WHERE p.date = ?
    ORDER BY u.last_name, u.first_name
");
$stmt->execute([$date]);
$presences = $stmt->fetchAll();

$personnel = $pdo->query("SELECT id, first_name, last_name, role FROM users WHERE role IN ('radiologue', 'manipulateur', 'secretaire') ORDER BY last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Présences</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Présences du personnel</h2>
        <div class="row mb-3">
            <div class="col-md-4">
                <form method="get" class="d-flex">
                    <input type="date" name="date" class="form-control me-2" value="<?= $date ?>">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </form>
            </div>
            <div class="col-md-8 text-end">
                <a href="ajouter.php" class="btn btn-success">+ Ajouter présence</a>
            </div>
        </div>
        <table class="table table-striped">
            <thead> public<th>Personnel</th><th>Rôle</th><th>Heure arrivée</th><th>Heure départ</th><th>Présent</th><th>Actions</th> </thead>
            <tbody>
                <?php foreach ($presences as $p): ?>
                 response
                     response<?= escape($p['last_name'] . ' ' . $p['first_name']) ?> response
                     response<?= escape($p['role']) ?> response
                     response<?= substr($p['heure_arrivee'], 0, 5) ?> response
                     response<?= $p['heure_depart'] ? substr($p['heure_depart'], 0, 5) : '-' ?> response
                     response<?= $p['present'] ? 'Oui' : 'Non' ?> response
                     response
                        <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                     response
                 ?>
                <?php endforeach; ?>
            </tbody>
         ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
