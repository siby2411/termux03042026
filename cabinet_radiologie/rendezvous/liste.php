<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$date_filter = $_GET['date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT r.*, 
           CONCAT(u.last_name, ' ', u.first_name) as patient_nom,
           e.nom as examen_nom,
           CONCAT(ru.last_name, ' ', ru.first_name) as radiologue_nom,
           CONCAT(mu.last_name, ' ', mu.first_name) as manipulateur_nom,
           eq.nom as equipement_nom
    FROM rendezvous r
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN examens e ON r.examen_id = e.id
    LEFT JOIN radiologues rad ON r.radiologue_id = rad.id
    LEFT JOIN users ru ON rad.user_id = ru.id
    JOIN manipulateurs man ON r.manipulateur_id = man.id
    JOIN users mu ON man.user_id = mu.id
    JOIN equipements eq ON r.equipement_id = eq.id
    WHERE r.date = ?
    ORDER BY r.heure_debut
");
$stmt->execute([$date_filter]);
$rendezvous = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Rendez-vous</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Rendez-vous</h2>
        <div class="row mb-3">
            <div class="col-md-4">
                <form method="get" class="d-flex">
                    <input type="date" name="date" class="form-control me-2" value="<?= $date_filter ?>">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </form>
            </div>
            <div class="col-md-8 text-end">
                <a href="ajouter.php" class="btn btn-success">+ Nouveau rendez-vous</a>
            </div>
        </div>
        <table class="table table-striped">
            <thead>
                <tr><th>Patient</th><th>Examen</th><th>Heure</th><th>Radiologue</th><th>Manipulateur</th><th>Équipement</th><th>Statut</th><th>Actions</th>
            </thead>
            <tbody>
                <?php foreach ($rendezvous as $r): ?>
                <tr>
                    <td><?= escape($r['patient_nom']) ?></td>
                    <td><?= escape($r['examen_nom']) ?></td>
                    <td><?= substr($r['heure_debut'], 0, 5) . ' - ' . substr($r['heure_fin'], 0, 5) ?></td>
                    <td><?= escape($r['radiologue_nom'] ?? '-') ?></td>
                    <td><?= escape($r['manipulateur_nom']) ?></td>
                    <td><?= escape($r['equipement_nom']) ?></td>
                    <td><?= escape($r['statut']) ?></td>
                    <td>
                        <a href="fiche.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">Voir</a>
                        <a href="modifier.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                        <?php if ($r['statut'] != 'termine'): ?>
                        <a href="../comptes_rendus/ajouter.php?rendezvous_id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">CR</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
