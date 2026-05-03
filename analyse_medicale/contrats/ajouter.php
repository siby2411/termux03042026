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
    $type_contrat = $_POST['type_contrat'] ?? '';
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? null;
    $salaire_base = $_POST['salaire_base'] ?? 0;
    $taux_horaire = $_POST['taux_horaire'] ?? null;
    $avantages = trim($_POST['avantages'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;

    if (!$personnel_id || !$type_contrat || !$date_debut || !$salaire_base) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contrats_personnel (personnel_id, type_contrat, date_debut, date_fin, salaire_base, taux_horaire, avantages, actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$personnel_id, $type_contrat, $date_debut, $date_fin, $salaire_base, $taux_horaire, $avantages, $actif]);
            $success = "Contrat ajouté.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

$personnel = $pdo->query("SELECT id, first_name, last_name FROM users WHERE role IN ('biologiste','technicien','secretaire') ORDER BY last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter contrat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un contrat</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3"><label>Personnel *</label><select name="personnel_id" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($personnel as $p): ?>
                <option value="<?= $p['id'] ?>"><?= escape($p['last_name'] . ' ' . $p['first_name']) ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="mb-3"><label>Type contrat *</label><select name="type_contrat" class="form-control" required>
                <option value="CDI">CDI</option><option value="CDD">CDD</option><option value="STAGE">Stage</option><option value="PRESTATION">Prestation</option>
            </select></div>
            <div class="mb-3"><label>Date début *</label><input type="date" name="date_debut" class="form-control" required></div>
            <div class="mb-3"><label>Date fin</label><input type="date" name="date_fin" class="form-control"></div>
            <div class="mb-3"><label>Salaire base (FCFA) *</label><input type="number" name="salaire_base" class="form-control" step="1000" required></div>
            <div class="mb-3"><label>Taux horaire (FCFA)</label><input type="number" name="taux_horaire" class="form-control" step="100"></div>
            <div class="mb-3"><label>Avantages</label><textarea name="avantages" class="form-control" rows="2"></textarea></div>
            <div class="form-check mb-3"><input type="checkbox" name="actif" class="form-check-input" checked> <label class="form-check-label">Actif</label></div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
