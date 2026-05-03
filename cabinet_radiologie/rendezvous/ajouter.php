<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

$patients = $pdo->query("SELECT p.id, CONCAT(u.last_name, ' ', u.first_name) as nom FROM patients p JOIN users u ON p.user_id = u.id ORDER BY u.last_name")->fetchAll();
$examens = $pdo->query("SELECT id, nom, duree_estimee, tarif FROM examens WHERE actif = 1 ORDER BY nom")->fetchAll();
$radiologues = $pdo->query("SELECT r.id, CONCAT(u.last_name, ' ', u.first_name) as nom FROM radiologues r JOIN users u ON r.user_id = u.id WHERE r.actif = 1 ORDER BY u.last_name")->fetchAll();
$manipulateurs = $pdo->query("SELECT m.id, CONCAT(u.last_name, ' ', u.first_name) as nom FROM manipulateurs m JOIN users u ON m.user_id = u.id WHERE m.actif = 1 ORDER BY u.last_name")->fetchAll();
$equipements = $pdo->query("SELECT id, nom FROM equipements WHERE statut = 'OPERATIONNEL' ORDER BY nom")->fetchAll();

$statuts = ['programme', 'confirme', 'arrive', 'en_cours', 'termine', 'annule', 'reporte'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? 0;
    $examen_id = $_POST['examen_id'] ?? 0;
    $radiologue_id = $_POST['radiologue_id'] ?? null;
    $manipulateur_id = $_POST['manipulateur_id'] ?? 0;
    $equipement_id = $_POST['equipement_id'] ?? 0;
    $date = $_POST['date'] ?? '';
    $heure_debut = $_POST['heure_debut'] ?? '';
    $heure_fin = $_POST['heure_fin'] ?? '';
    $statut = $_POST['statut'] ?? 'programme';
    $motif = trim($_POST['motif'] ?? 'Consultation');
    $notes = trim($_POST['notes'] ?? '');
    $besoin_specifique = trim($_POST['besoin_specifique'] ?? '');

    if (!$patient_id || !$examen_id || !$manipulateur_id || !$equipement_id || !$date || !$heure_debut || !$heure_fin) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO rendezvous (patient_id, examen_id, radiologue_id, manipulateur_id, equipement_id, date, heure_debut, heure_fin, statut, motif, notes, besoin_specifique) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $examen_id, $radiologue_id ?: null, $manipulateur_id, $equipement_id, $date, $heure_debut, $heure_fin, $statut, $motif, $notes, $besoin_specifique]);
            $rdv_id = $pdo->lastInsertId();

            // Créer automatiquement une facture
            $examen = $pdo->prepare("SELECT tarif FROM examens WHERE id = ?")->execute([$examen_id])->fetch();
            if ($examen && $examen['tarif'] > 0) {
                $pdo->prepare("INSERT INTO factures (patient_id, rendezvous_id, total_ht, tva, total_ttc) VALUES (?, ?, ?, 0, ?)")->execute([$patient_id, $rdv_id, $examen['tarif'], $examen['tarif']]);
            }
            $pdo->commit();
            $success = "Rendez-vous ajouté avec succès. Une facture a été créée automatiquement.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Ajouter rendez-vous</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script>
function calculerHeureFin() {
    const debut = document.getElementById('heure_debut').value;
    const duree = parseInt(document.getElementById('duree_estimee').value);
    if (debut && duree) {
        const [h, m] = debut.split(':');
        let totalMinutes = parseInt(h) * 60 + parseInt(m) + duree;
        const finH = Math.floor(totalMinutes / 60);
        const finM = totalMinutes % 60;
        document.getElementById('heure_fin').value = String(finH).padStart(2, '0') + ':' + String(finM).padStart(2, '0');
    }
}
function chargerDuree() {
    const examenId = document.getElementById('examen_id').value;
    if (examenId) {
        fetch('get_duree.php?id=' + examenId)
            .then(r => r.json())
            .then(data => {
                document.getElementById('duree_estimee').value = data.duree;
                calculerHeureFin();
            });
    }
}
</script>
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un rendez-vous</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post" onchange="calculerHeureFin()">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Patient *</label><select name="patient_id" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= escape($p['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-6 mb-3"><label>Examen *</label><select name="examen_id" id="examen_id" class="form-control" required onchange="chargerDuree()">
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($examens as $e): ?>
                    <option value="<?= $e['id'] ?>" data-duree="<?= $e['duree_estimee'] ?>"><?= escape($e['nom']) ?> (<?= $e['duree_estimee'] ?> min - <?= formatMoney($e['tarif']) ?>)</option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-4 mb-3"><label>Radiologue</label><select name="radiologue_id" class="form-control">
                    <option value="">-- Optionnel --</option>
                    <?php foreach ($radiologues as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= escape($r['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-4 mb-3"><label>Manipulateur *</label><select name="manipulateur_id" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($manipulateurs as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= escape($m['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-4 mb-3"><label>Équipement *</label><select name="equipement_id" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($equipements as $eq): ?>
                    <option value="<?= $eq['id'] ?>"><?= escape($eq['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-4 mb-3"><label>Date *</label><input type="date" name="date" class="form-control" required></div>
                <div class="col-md-4 mb-3"><label>Heure début *</label><input type="time" name="heure_debut" id="heure_debut" class="form-control" required onchange="calculerHeureFin()"></div>
                <div class="col-md-4 mb-3"><label>Heure fin *</label><input type="time" name="heure_fin" id="heure_fin" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Statut</label><select name="statut" class="form-control">
                    <?php foreach ($statuts as $s): ?>
                    <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-6 mb-3"><label>Motif</label><input type="text" name="motif" class="form-control" value="Consultation"></div>
                <div class="col-12 mb-3"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                <div class="col-12 mb-3"><label>Besoin spécifique</label><textarea name="besoin_specifique" class="form-control" rows="2"></textarea></div>
            </div>
            <input type="hidden" id="duree_estimee" value="0">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
