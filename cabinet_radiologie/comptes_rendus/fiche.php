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
    SELECT cr.*, 
           CONCAT(u.last_name, ' ', u.first_name) as patient_nom,
           e.nom as examen_nom,
           CONCAT(ru.last_name, ' ', ru.first_name) as radiologue_nom,
           r.date as rdv_date
    FROM comptes_rendus cr
    JOIN rendezvous r ON cr.rendezvous_id = r.id
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN examens e ON r.examen_id = e.id
    JOIN radiologues rad ON cr.radiologue_id = rad.id
    JOIN users ru ON rad.user_id = ru.id
    WHERE cr.id = ?
");
$stmt->execute([$id]);
$cr = $stmt->fetch();

if (!$cr) { die("Compte rendu introuvable."); }
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Compte rendu</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Compte rendu d'examen</h2>
        <div class="card mb-4"><div class="card-header bg-primary text-white">Informations patient</div>
            <div class="card-body">
                <p><strong>Patient :</strong> <?= escape($cr['patient_nom']) ?></p>
                <p><strong>Examen :</strong> <?= escape($cr['examen_nom']) ?></p>
                <p><strong>Date :</strong> <?= formatDate($cr['rdv_date']) ?></p>
                <p><strong>Radiologue :</strong> Dr. <?= escape($cr['radiologue_nom']) ?></p>
                <p><strong>Date rédaction :</strong> <?= formatDateTime($cr['date_redaction']) ?></p>
                <?php if ($cr['signe']): ?><p><strong>Signé le :</strong> <?= formatDateTime($cr['date_signature']) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="card mb-4"><div class="card-header bg-secondary text-white">Compte rendu</div>
            <div class="card-body">
                <h5>Indication</h5><p><?= nl2br(escape($cr['indication'])) ?></p>
                <h5>Technique</h5><p><?= nl2br(escape($cr['technique'])) ?></p>
                <?php if ($cr['comparaison']): ?><h5>Comparaison</h5><p><?= nl2br(escape($cr['comparaison'])) ?></p><?php endif; ?>
                <h5>Résultats</h5><p><?= nl2br(escape($cr['resultats'])) ?></p>
                <h5>Conclusion</h5><p><?= nl2br(escape($cr['conclusion'])) ?></p>
                <?php if ($cr['recommandations']): ?><h5>Recommandations</h5><p><?= nl2br(escape($cr['recommandations'])) ?></p><?php endif; ?>
            </div>
        </div>
        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <?php if (!$cr['signe'] && ($_SESSION['role'] == 'radiologue' || $_SESSION['role'] == 'admin')): ?>
        <a href="signer.php?id=<?= $cr['id'] ?>" class="btn btn-success" onclick="return confirm('Signer ce compte rendu ?')">Signer</a>
        <?php endif; ?>
        <a href="modifier.php?id=<?= $cr['id'] ?>" class="btn btn-warning">Modifier</a>
        <a href="supprimer.php?id=<?= $cr['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
