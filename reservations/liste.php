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

// Statistiques
$stats = [];
$stats['total'] = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$stats['confirmees'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'Confirmée'")->fetchColumn();
$stats['en_cours'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'En cours'")->fetchColumn();
$stats['terminees'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'Terminée'")->fetchColumn();
$stats['annulees'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'Annulée'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservations - OMEGA Hôtel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card { transition: transform 0.2s; border-left: 4px solid; cursor: pointer; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-confirmee { border-left-color: #2ecc71; }
        .stat-en_cours { border-left-color: #f39c12; }
        .stat-terminee { border-left-color: #3498db; }
        .stat-annulee { border-left-color: #e74c3c; }
    </style>
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-alt me-2"></i>Gestion des réservations</h2>
            <a href="ajouter.php" class="btn btn-success"><i class="fas fa-plus me-1"></i> Nouvelle réservation</a>
        </div>

        <!-- Cartes statistiques -->
        <div class="row mb-4">
            <div class="col-md-2"><div class="card stat-card stat-confirmee"><div class="card-body"><h6>Total</h6><h3><?= $stats['total'] ?></h3></div></div></div>
            <div class="col-md-2"><div class="card stat-card stat-confirmee"><div class="card-body"><h6>Confirmées</h6><h3><?= $stats['confirmees'] ?></h3></div></div></div>
            <div class="col-md-2"><div class="card stat-card stat-en_cours"><div class="card-body"><h6>En cours</h6><h3><?= $stats['en_cours'] ?></h3></div></div></div>
            <div class="col-md-2"><div class="card stat-card stat-terminee"><div class="card-body"><h6>Terminées</h6><h3><?= $stats['terminees'] ?></h3></div></div></div>
            <div class="col-md-2"><div class="card stat-card stat-annulee"><div class="card-body"><h6>Annulées</h6><h3><?= $stats['annulees'] ?></h3></div></div></div>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-3">
                        <label>Filtrer par statut</label>
                        <select name="statut" class="form-select" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            <option value="Confirmée" <?= $statut_filter == 'Confirmée' ? 'selected' : '' ?>>Confirmée</option>
                            <option value="En cours" <?= $statut_filter == 'En cours' ? 'selected' : '' ?>>En cours</option>
                            <option value="Terminée" <?= $statut_filter == 'Terminée' ? 'selected' : '' ?>>Terminée</option>
                            <option value="Annulée" <?= $statut_filter == 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-2"><label>&nbsp;</label><a href="liste.php" class="btn btn-secondary d-block">Réinitialiser</a></div>
                </form>
            </div>
        </div>

        <!-- Liste des réservations -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>ID</th><th>Client</th><th>Chambre</th><th>Arrivée</th><th>Départ</th><th>Nuits</th><th>Prix total</th><th>Statut</th><th>Actions</th> </thead>
                        <tbody>
                            <?php foreach ($reservations as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><strong><?= escape($r['client_prenom'] . ' ' . $r['client_nom']) ?></strong></td>
                                <td><?= escape($r['chambre_numero']) ?> (<?= escape($r['chambre_type']) ?>)</td>
                                <td><?= formatDate($r['date_arrivee']) ?></td>
                                <td><?= formatDate($r['date_depart']) ?></td>
                                <td><?= $r['nb_nuits'] ?></td>
                                <td class="fw-bold"><?= formatMoney($r['prix_total']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $r['statut'] == 'Confirmée' ? 'success' : ($r['statut'] == 'En cours' ? 'warning' : ($r['statut'] == 'Terminée' ? 'info' : 'danger')) ?>">
                                        <?= escape($r['statut']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="fiche.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info" title="Voir"><i class="fas fa-eye"></i></a>
                                    <a href="modifier.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning" title="Modifier"><i class="fas fa-edit"></i></a>
                                    <a href="supprimer.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette réservation ?')" title="Supprimer"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($reservations)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">Aucune réservation trouvée</td></tr>
                            <?php endif; ?>
                        </tbody>
                     </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
