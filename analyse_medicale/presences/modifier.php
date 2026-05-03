<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: liste.php');
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM presences WHERE id = ?");
$stmt->execute([$id]);
$presence = $stmt->fetch();

if (!$presence) {
    die("Présence introuvable.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $heure_arrivee = $_POST['heure_arrivee'] ?? '';
    $heure_depart = $_POST['heure_depart'] ?? '';
    $present = isset($_POST['present']) ? 1 : 0;

    if (!$date || !$heure_arrivee) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE presences SET date = ?, heure_arrivee = ?, heure_depart = ?, present = ? WHERE id = ?");
            $stmt->execute([$date, $heure_arrivee, $heure_depart, $present, $id]);
            $success = "Présence modifiée.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier présence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier présence</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3"><label>Date *</label><input type="date" name="date" class="form-control" value="<?= $presence['date'] ?>" required></div>
            <div class="mb-3"><label>Heure arrivée *</label><input type="time" name="heure_arrivee" class="form-control" value="<?= $presence['heure_arrivee'] ?>" required></div>
            <div class="mb-3"><label>Heure départ</label><input type="time" name="heure_depart" class="form-control" value="<?= $presence['heure_depart'] ?>"></div>
            <div class="form-check mb-3"><input type="checkbox" name="present" class="form-check-input" <?= $presence['present'] ? 'checked' : '' ?>> <label class="form-check-label">Présent</label></div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
