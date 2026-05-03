<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personnel_id = $_POST['personnel_id'] ?? 0;
    $date = $_POST['date'] ?? date('Y-m-d');
    $heure_arrivee = $_POST['heure_arrivee'] ?? '';
    $heure_depart = $_POST['heure_depart'] ?? '';
    $present = isset($_POST['present']) ? 1 : 0;

    if (!$personnel_id || !$date || !$heure_arrivee) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO presences (personnel_id, date, heure_arrivee, heure_depart, present) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$personnel_id, $date, $heure_arrivee, $heure_depart, $present]);
            $success = "Présence enregistrée.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

$personnel = $pdo->query("SELECT id, first_name, last_name, role FROM users WHERE role IN ('biologiste','technicien','secretaire') ORDER BY last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter présence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter une présence</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3"><label>Personnel *</label><select name="personnel_id" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($personnel as $p): ?>
                <option value="<?= $p['id'] ?>"><?= escape($p['last_name'] . ' ' . $p['first_name']) ?> (<?= escape($p['role']) ?>)</option>
                <?php endforeach; ?>
            </select></div>
            <div class="mb-3"><label>Date *</label><input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
            <div class="mb-3"><label>Heure arrivée *</label><input type="time" name="heure_arrivee" class="form-control" required></div>
            <div class="mb-3"><label>Heure départ</label><input type="time" name="heure_depart" class="form-control"></div>
            <div class="form-check mb-3"><input type="checkbox" name="present" class="form-check-input" checked> <label class="form-check-label">Présent</label></div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
