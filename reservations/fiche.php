<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("
    SELECT r.*, c.nom as client_nom, c.prenom as client_prenom, c.telephone, c.email,
           ch.numero as chambre_numero, ch.type as chambre_type, ch.prix_nuit
    FROM reservations r
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) { die("Réservation introuvable."); }
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Fiche réservation</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-file-alt me-2"></i>Fiche réservation #<?= $r['id'] ?></h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">Informations client</div>
                    <div class="card-body">
                        <p><strong>Nom :</strong> <?= escape($r['client_prenom'] . ' ' . $r['client_nom']) ?></p>
                        <p><strong>Téléphone :</strong> <?= escape($r['telephone']) ?></p>
                        <p><strong>Email :</strong> <?= escape($r['email']) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">Informations séjour</div>
                    <div class="card-body">
                        <p><strong>Chambre :</strong> <?= escape($r['chambre_numero']) ?> (<?= escape($r['chambre_type']) ?>)</p>
                        <p><strong>Arrivée :</strong> <?= formatDate($r['date_arrivee']) ?></p>
                        <p><strong>Départ :</strong> <?= formatDate($r['date_depart']) ?></p>
                        <p><strong>Nombre de nuits :</strong> <?= $r['nb_nuits'] ?></p>
                        <p><strong>Prix par nuit :</strong> <?= formatMoney($r['prix_nuit']) ?></p>
                        <p><strong>Total :</strong> <?= formatMoney($r['prix_total']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">Informations réservation</div>
            <div class="card-body">
                <p><strong>Statut :</strong> <span class="badge bg-<?= $r['statut'] == 'Confirmée' ? 'success' : ($r['statut'] == 'En cours' ? 'warning' : ($r['statut'] == 'Terminée' ? 'info' : 'danger')) ?>"><?= escape($r['statut']) ?></span></p>
                <p><strong>Mode de paiement :</strong> <?= escape($r['mode_paiement']) ?></p>
                <p><strong>Notes :</strong> <?= nl2br(escape($r['notes'])) ?></p>
                <p><strong>Date de création :</strong> <?= formatDateTime($r['created_at']) ?></p>
            </div>
        </div>
        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <a href="modifier.php?id=<?= $r['id'] ?>" class="btn btn-warning">Modifier</a>
        <a href="supprimer.php?id=<?= $r['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette réservation ?')">Supprimer</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
