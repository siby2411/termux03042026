<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();

$statut_filter = $_GET['statut'] ?? '';
$sql = "SELECT r.*, c.nom as client_nom, c.prenom as client_prenom, ch.numero as chambre_numero, ch.type as chambre_type
        FROM reservations r
        JOIN clients c ON r.client_id = c.id
        JOIN chambres ch ON r.chambre_id = ch.id
        WHERE 1=1";
$params = [];

if ($statut_filter) {
    $sql .= " AND r.statut = ?";
    $params[] = $statut_filter;
}
$sql .= " ORDER BY r.date_arrivee DESC, r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Réservations</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-calendar-alt me-2"></i>Gestion des réservations</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouvelle réservation</a>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead> <tr><th>Client</th><th>Chambre</th><th>Arrivée</th><th>Départ</th><th>Nuits</th><th>Total</th><th>Statut</th><th>Actions</th></tr> </thead>
                <tbody>
                    <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?= escape($r['client_prenom'] . ' ' . $r['client_nom']) ?></td>
                        <td><?= escape($r['chambre_numero']) ?> (<?= escape($r['chambre_type']) ?>)</td>
                        <td><?= formatDate($r['date_arrivee']) ?></td>
                        <td><?= formatDate($r['date_depart']) ?></td>
                        <td><?= $r['nb_nuits'] ?></td>
                        <td class="fw-bold"><?= formatMoney($r['prix_total']) ?></td>
                        <td><span class="badge bg-<?= $r['statut'] == 'Confirmée' ? 'success' : 'warning' ?>"><?= escape($r['statut']) ?></span></td>
                        <td><a href="modifier.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Modifier</a> <a href="supprimer.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reservations)): ?><tr><td colspan="8" class="text-center">Aucune réservation</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
