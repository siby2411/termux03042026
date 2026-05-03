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
$techniciens = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as nom FROM users WHERE role = 'technicien' ORDER BY last_name")->fetchAll();
$analyses = $pdo->query("SELECT id, nom FROM analyses WHERE actif = 1 ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? 0;
    $medecin_id = $_POST['medecin_id'] ?? null;
    $date_prelevement = $_POST['date_prelevement'] ?? '';
    $technicien_id = $_POST['technicien_id'] ?? null;
    $lieu_prelevement = $_POST['lieu_prelevement'] ?? 'Laboratoire';
    $statut = $_POST['statut'] ?? 'programme';
    $observations = trim($_POST['observations'] ?? '');
    $analyses_choisies = $_POST['analyses'] ?? [];

    if (!$patient_id || !$date_prelevement || empty($analyses_choisies)) {
        $error = "Veuillez remplir tous les champs obligatoires (patient, date, au moins une analyse).";
    } else {
        try {
            $pdo->beginTransaction();
            $code_barre = 'BAR' . date('Ymd') . rand(1000, 9999);
            $stmt = $pdo->prepare("INSERT INTO prelevements (patient_id, medecin_prescripteur_id, date_prelevement, technicien_id, lieu_prelevement, statut, observations, code_barre) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $medecin_id ?: null, $date_prelevement, $technicien_id ?: null, $lieu_prelevement, $statut, $observations, $code_barre]);
            $prelevement_id = $pdo->lastInsertId();

            $insert = $pdo->prepare("INSERT INTO prelevement_analyses (prelevement_id, analyse_id) VALUES (?, ?)");
            foreach ($analyses_choisies as $aid) {
                $insert->execute([$prelevement_id, $aid]);
                $pdo->prepare("INSERT INTO analyses_realisees (prelevement_id, analyse_id, technicien_id, statut) VALUES (?, ?, ?, 'en_attente')")->execute([$prelevement_id, $aid, $technicien_id ?: null]);
            }
            $pdo->commit();
            $success = "Prélèvement ajouté avec succès. Code barre : $code_barre";
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
    <title>Ajouter prélèvement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un prélèvement</h2>
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
                <div class="col-md-6 mb-3"><label>Date et heure de prélèvement *</label><input type="datetime-local" name="date_prelevement" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Technicien</label><select name="technicien_id" class="form-control">
                    <option value="">-- Optionnel --</option>
                    <?php foreach ($techniciens as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= escape($t['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-6 mb-3"><label>Lieu de prélèvement</label><input type="text" name="lieu_prelevement" class="form-control" value="Laboratoire"></div>
                <div class="col-md-6 mb-3"><label>Statut</label><select name="statut" class="form-control">
                    <option value="programme">Programmé</option><option value="effectue">Effectué</option><option value="refuse">Refusé</option><option value="reporte">Reporté</option>
                </select></div>
                <div class="col-12 mb-3"><label>Analyses * (Ctrl+clic pour multiple)</label><select name="analyses[]" multiple class="form-control" size="8" required>
                    <?php foreach ($analyses as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= escape($a['nom']) ?></option>
                    <?php endforeach; ?>
                </select><small class="text-muted">Maintenez Ctrl pour sélectionner plusieurs analyses</small></div>
                <div class="col-12 mb-3"><label>Observations</label><textarea name="observations" class="form-control" rows="2"></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
