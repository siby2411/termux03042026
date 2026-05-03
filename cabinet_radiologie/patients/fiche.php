<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.adresse FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { die("Patient introuvable."); }
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Fiche patient</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4"><h2>Fiche patient : <?= escape($p['last_name'] . ' ' . $p['first_name']) ?></h2>
    <table class="table table-bordered"><tr><th>Code</th><td><?= escape($p['code_patient']) ?></td></tr>
    <tr><th>Date naissance</th><td><?= formatDate($p['date_naissance']) ?></td></tr>
    <tr><th>Téléphone</th><td><?= escape($p['phone']) ?></td></tr>
    <tr><th>Email</th><td><?= escape($p['email']) ?></td></tr>
    <tr><th>Adresse</th><td><?= nl2br(escape($p['adresse'])) ?></td></tr>
    <tr><th>Assurance</th><td><?= escape($p['assurance']) ?></td></tr></table>
    <a href="liste.php" class="btn btn-secondary">Retour</a></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
