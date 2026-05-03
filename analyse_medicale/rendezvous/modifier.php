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
$stmt = $pdo->prepare("SELECT * FROM rendezvous WHERE id = ?");
$stmt->execute([$id]);
$rdv = $stmt->fetch();

if (!$rdv) {
    die("Rendez-vous introuvable.");
}

// Récupérer les patients pour le select
$patients = $pdo->query("SELECT p.id, CONCAT(u.last_name, ' ', u.first_name) as nom FROM patients p JOIN users u ON p.user_id = u.id ORDER BY u.last_name")->fetchAll();

// Récupérer les analyses associées
$stmt = $pdo->prepare("SELECT analyse_id FROM rendezvous_analyses WHERE rendezvous_id = ?");
$stmt->execute([$id]);
$analyses_rdv = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Récupérer toutes les analyses pour le formulaire
$all_analyses = $pdo->query("SELECT id, nom FROM analyses WHERE actif = 1 ORDER BY nom")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? 0;
    $date = $_POST['date'] ?? '';
    $heure_debut = $_POST['heure_debut'] ?? '';
    $heure_fin = $_POST['heure_fin'] ?? '';
    $statut = $_POST['statut'] ?? 'programme';
    $notes = trim($_POST['notes'] ?? '');
    $besoin_specifique = trim($_POST['besoin_specifique'] ?? '');
    $analyses = $_POST['analyses'] ?? [];

    if (!$patient_id || !$date || !$heure_debut || !$heure_fin) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE rendezvous SET patient_id = ?, date = ?, heure_debut = ?, heure_fin = ?, statut = ?, notes = ?, besoin_specifique = ? WHERE id = ?");
            $stmt->execute([$patient_id, $date, $heure_debut, $heure_fin, $statut, $notes, $besoin_specifique, $id]);

            // Mettre à jour les analyses
            $stmt = $pdo->prepare("DELETE FROM rendezvous_analyses WHERE rendezvous_id = ?");
            $stmt->execute([$id]);
            $insert = $pdo->prepare("INSERT INTO rendezvous_analyses (rendezvous_id, analyse_id) VALUES (?, ?)");
            foreach ($analyses as $aid) {
                $insert->execute([$id, $aid]);
            }
            $pdo->commit();
            $success = "Rendez-vous modifié.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier rendez-vous</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Patient *</label><select name="patient_id" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $rdv['patient_id'] == $p['id'] ? 'selected' : '' ?>><?= escape($p['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-6 mb-3"><label>Date *</label><input type="date" name="date" class="form-control" value="<?= $rdv['date'] ?>" required></div>
                <div class="col-md-6 mb-3"><label>Heure début *</label><input type="time" name="heure_debut" class="form-control" value="<?= $rdv['heure_debut'] ?>" required></div>
                <div class="col-md-6 mb-3"><label>Heure fin *</label><input type="time" name="heure_fin" class="form-control" value="<?= $rdv['heure_fin'] ?>" required></div>
                <div class="col-md-6 mb-3"><label>Statut</label><select name="statut" class="form-control">
                    <?php $statuses = ['programme','confirme','arrive','prelevement','termine','annule','reporte']; ?>
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $rdv['statut'] == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-12 mb-3"><label>Analyses</label><select name="analyses[]" multiple class="form-control">
                    <?php foreach ($all_analyses as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= in_array($a['id'], $analyses_rdv) ? 'selected' : '' ?>><?= escape($a['nom']) ?></option>
                    <?php endforeach; ?>
                </select><small class="text-muted">Maintenir Ctrl pour sélection multiple</small></div>
                <div class="col-md-12 mb-3"><label>Notes</label><textarea name="notes" class="form-control" rows="2"><?= escape($rdv['notes']) ?></textarea></div>
                <div class="col-md-12 mb-3"><label>Besoin spécifique</label><textarea name="besoin_specifique" class="form-control" rows="2"><?= escape($rdv['besoin_specifique']) ?></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
