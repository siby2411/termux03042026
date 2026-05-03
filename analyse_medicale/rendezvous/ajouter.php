<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

$patients = $pdo->query("SELECT p.id, CONCAT(u.last_name, ' ', u.first_name) as nom FROM patients p JOIN users u ON p.user_id = u.id ORDER BY u.last_name")->fetchAll();
$medecins = $pdo->query("SELECT id, CONCAT(prenom, ' ', nom) as nom FROM medecins_prescripteurs WHERE actif = 1 ORDER BY nom")->fetchAll();
$analyses = $pdo->query("SELECT id, nom FROM analyses WHERE actif = 1 ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? 0;
    $medecin_id = $_POST['medecin_id'] ?? null;
    $date = $_POST['date'] ?? '';
    $heure_debut = $_POST['heure_debut'] ?? '';
    $heure_fin = $_POST['heure_fin'] ?? '';
    $statut = $_POST['statut'] ?? 'programme';
    $notes = trim($_POST['notes'] ?? '');
    $besoin_specifique = trim($_POST['besoin_specifique'] ?? '');
    $analyses_choisies = $_POST['analyses'] ?? [];

    if (!$patient_id || !$date || !$heure_debut || !$heure_fin || empty($analyses_choisies)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO rendezvous (patient_id, medecin_prescripteur_id, date, heure_debut, heure_fin, statut, notes, besoin_specifique) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $medecin_id ?: null, $date, $heure_debut, $heure_fin, $statut, $notes, $besoin_specifique]);
            $rdv_id = $pdo->lastInsertId();

            $insert = $pdo->prepare("INSERT INTO rendezvous_analyses (rendezvous_id, analyse_id) VALUES (?, ?)");
            foreach ($analyses_choisies as $aid) {
                $insert->execute([$rdv_id, $aid]);
            }
            $pdo->commit();
            $success = "Rendez-vous ajouté avec succès.";
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
    <title>Ajouter rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un rendez-vous</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Patient *</label><select name="patient_id" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= escape($p['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-6 mb-3"><label>Médecin prescripteur</label><select name="medecin_id" class="form-control">
                    <option value="">-- Optionnel --</option>
                    <?php foreach ($medecins as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= escape($m['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-4 mb-3"><label>Date *</label><input type="date" name="date" class="form-control" required></div>
                <div class="col-md-4 mb-3"><label>Heure début *</label><input type="time" name="heure_debut" class="form-control" required></div>
                <div class="col-md-4 mb-3"><label>Heure fin *</label><input type="time" name="heure_fin" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Statut</label><select name="statut" class="form-control">
                    <?php $statuses = ['programme','confirme','arrive','prelevement','termine','annule','reporte']; ?>
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-12 mb-3"><label>Analyses * (Ctrl+clic)</label><select name="analyses[]" multiple class="form-control" size="6" required>
                    <?php foreach ($analyses as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= escape($a['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-12 mb-3"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                <div class="col-12 mb-3"><label>Besoin spécifique</label><textarea name="besoin_specifique" class="form-control" rows="2"></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
