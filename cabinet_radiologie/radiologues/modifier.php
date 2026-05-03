<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT r.*, u.id as user_id, u.first_name, u.last_name, u.email, u.phone FROM radiologues r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { die("Radiologue introuvable."); }
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? ''); $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? ''); $phone = trim($_POST['phone'] ?? '');
    $specialite = trim($_POST['specialite'] ?? ''); $numero_ordre = trim($_POST['numero_ordre'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;
    if (!$first_name || !$last_name || !$specialite || !$numero_ordre) { $error = "Champs obligatoires."; }
    else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=? WHERE id=?")->execute([$first_name, $last_name, $email, $phone, $r['user_id']]);
            $pdo->prepare("UPDATE radiologues SET specialite=?, numero_ordre=?, actif=? WHERE id=?")->execute([$specialite, $numero_ordre, $actif, $id]);
            $pdo->commit(); $success = "Radiologue modifié.";
        } catch (PDOException $e) { $pdo->rollBack(); $error = "Erreur: " . $e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Modifier radiologue</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4"><h2>Modifier radiologue</h2>
    <?php if($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
    <form method="post"><div class="row">
        <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="last_name" value="<?= escape($r['last_name']) ?>" class="form-control" required></div>
        <div class="col-md-6 mb-3"><label>Prénom *</label><input type="text" name="first_name" value="<?= escape($r['first_name']) ?>" class="form-control" required></div>
        <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" value="<?= escape($r['email']) ?>" class="form-control"></div>
        <div class="col-md-6 mb-3"><label>Téléphone</label><input type="text" name="phone" value="<?= escape($r['phone']) ?>" class="form-control"></div>
        <div class="col-md-6 mb-3"><label>Spécialité *</label><input type="text" name="specialite" value="<?= escape($r['specialite']) ?>" class="form-control" required></div>
        <div class="col-md-6 mb-3"><label>Numéro d'ordre *</label><input type="text" name="numero_ordre" value="<?= escape($r['numero_ordre']) ?>" class="form-control" required></div>
        <div class="col-md-12 mb-3"><div class="form-check"><input type="checkbox" name="actif" class="form-check-input" <?= $r['actif'] ? 'checked' : '' ?>> <label>Actif</label></div></div>
    </div><button type="submit" class="btn btn-primary">Enregistrer</button><a href="liste.php" class="btn btn-secondary">Annuler</a></form></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
