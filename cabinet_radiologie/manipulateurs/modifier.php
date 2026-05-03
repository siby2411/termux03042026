<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT m.*, u.id as user_id, u.first_name, u.last_name, u.email, u.phone FROM manipulateurs m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

if (!$m) { die("Manipulateur introuvable."); }

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $numero_licence = trim($_POST['numero_licence'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;

    if (!$first_name || !$last_name || !$qualification || !$numero_licence) {
        $error = "Champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=? WHERE id=?")->execute([$first_name, $last_name, $email, $phone, $m['user_id']]);
            $pdo->prepare("UPDATE manipulateurs SET qualification=?, numero_licence=?, actif=? WHERE id=?")->execute([$qualification, $numero_licence, $actif, $id]);
            $pdo->commit();
            $success = "Manipulateur modifié.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Modifier manipulateur</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier manipulateur : <?= escape($m['last_name'] . ' ' . $m['first_name']) ?></h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="last_name" value="<?= escape($m['last_name']) ?>" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Prénom *</label><input type="text" name="first_name" value="<?= escape($m['first_name']) ?>" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" value="<?= escape($m['email']) ?>" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Téléphone</label><input type="text" name="phone" value="<?= escape($m['phone']) ?>" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Qualification *</label><input type="text" name="qualification" value="<?= escape($m['qualification']) ?>" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Numéro licence *</label><input type="text" name="numero_licence" value="<?= escape($m['numero_licence']) ?>" class="form-control" required></div>
                <div class="col-md-12 mb-3"><div class="form-check"><input type="checkbox" name="actif" class="form-check-input" <?= $m['actif'] ? 'checked' : '' ?>> <label>Actif</label></div></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
