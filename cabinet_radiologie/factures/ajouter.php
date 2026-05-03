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
$rendezvous = $pdo->query("
    SELECT r.id, CONCAT(u.last_name, ' ', u.first_name) as patient_nom, e.nom as examen_nom, r.date 
    FROM rendezvous r
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN examens e ON r.examen_id = e.id
    WHERE r.id NOT IN (SELECT rendezvous_id FROM factures WHERE rendezvous_id IS NOT NULL)
    ORDER BY r.date DESC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? 0;
    $rendezvous_id = $_POST['rendezvous_id'] ?? null;
    $total_ht = (float)($_POST['total_ht'] ?? 0);
    $tva = (float)($_POST['tva'] ?? 0);
    $remise = (float)($_POST['remise'] ?? 0);
    $montant_assurance = (float)($_POST['montant_assurance'] ?? 0);
    $prise_en_charge_assurance = isset($_POST['prise_en_charge_assurance']) ? 1 : 0;
    $assurance = trim($_POST['assurance'] ?? '');

    if (!$patient_id || !$total_ht) {
        $error = "Veuillez remplir les champs obligatoires.";
    } else {
        try {
            $total_ttc = $total_ht + $tva - $remise;
            $montant_patient = $total_ttc - $montant_assurance;
            $stmt = $pdo->prepare("INSERT INTO factures (patient_id, rendezvous_id, total_ht, tva, total_ttc, remise, montant_assurance, montant_patient, prise_en_charge_assurance, assurance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $rendezvous_id ?: null, $total_ht, $tva, $total_ttc, $remise, $montant_assurance, $montant_patient, $prise_en_charge_assurance, $assurance]);
            $success = "Facture créée avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Créer facture</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script>
function calculerTotal() {
    const ht = parseFloat(document.getElementById('total_ht').value) || 0;
    const tva = parseFloat(document.getElementById('tva').value) || 0;
    const remise = parseFloat(document.getElementById('remise').value) || 0;
    const total = ht + tva - remise;
    document.getElementById('total_ttc_preview').innerText = total.toLocaleString() + ' FCFA';
}
</script>
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Créer une facture</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post" onchange="calculerTotal()" onkeyup="calculerTotal()">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Patient *</label><select name="patient_id" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= escape($p['nom']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-6 mb-3"><label>Rendez-vous associé</label><select name="rendezvous_id" class="form-control">
                    <option value="">-- Optionnel --</option>
                    <?php foreach ($rendezvous as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= escape($r['patient_nom']) ?> - <?= escape($r['examen_nom']) ?> (<?= formatDate($r['date']) ?>)</option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-4 mb-3"><label>Total HT (FCFA) *</label><input type="number" name="total_ht" id="total_ht" class="form-control" step="100" required></div>
                <div class="col-md-4 mb-3"><label>TVA (FCFA)</label><input type="number" name="tva" id="tva" class="form-control" step="100" value="0"></div>
                <div class="col-md-4 mb-3"><label>Remise (FCFA)</label><input type="number" name="remise" id="remise" class="form-control" step="100" value="0"></div>
                <div class="col-md-6 mb-3"><label>Montant pris en charge assurance</label><input type="number" name="montant_assurance" class="form-control" step="100" value="0"></div>
                <div class="col-md-6 mb-3"><div class="form-check mt-4"><input type="checkbox" name="prise_en_charge_assurance" class="form-check-input"> <label>Prise en charge assurance</label></div></div>
                <div class="col-md-6 mb-3"><label>Assurance</label><input type="text" name="assurance" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Total TTC :</label><h4 id="total_ttc_preview">0 FCFA</h4></div>
            </div>
            <button type="submit" class="btn btn-primary">Créer facture</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
