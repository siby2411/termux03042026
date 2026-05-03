<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM factures WHERE id = ?");
$stmt->execute([$id]);
$f = $stmt->fetch();

if (!$f) { die("Facture introuvable."); }

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_ht = (float)($_POST['total_ht'] ?? 0);
    $tva = (float)($_POST['tva'] ?? 0);
    $remise = (float)($_POST['remise'] ?? 0);
    $montant_assurance = (float)($_POST['montant_assurance'] ?? 0);
    $prise_en_charge_assurance = isset($_POST['prise_en_charge_assurance']) ? 1 : 0;
    $assurance = trim($_POST['assurance'] ?? '');

    if (!$total_ht) {
        $error = "Veuillez remplir le total HT.";
    } else {
        try {
            $total_ttc = $total_ht + $tva - $remise;
            $montant_patient = $total_ttc - $montant_assurance;
            $stmt = $pdo->prepare("UPDATE factures SET total_ht=?, tva=?, total_ttc=?, remise=?, montant_assurance=?, montant_patient=?, prise_en_charge_assurance=?, assurance=? WHERE id=?");
            $stmt->execute([$total_ht, $tva, $total_ttc, $remise, $montant_assurance, $montant_patient, $prise_en_charge_assurance, $assurance, $id]);
            $success = "Facture modifiée.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Modifier facture</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h2>Modifier facture <?= escape($f['numero_facture']) ?></h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post" onchange="calculerTotal()" onkeyup="calculerTotal()">
            <div class="row">
                <div class="col-md-4 mb-3"><label>Total HT (FCFA) *</label><input type="number" name="total_ht" id="total_ht" class="form-control" step="100" value="<?= $f['total_ht'] ?>" required></div>
                <div class="col-md-4 mb-3"><label>TVA (FCFA)</label><input type="number" name="tva" id="tva" class="form-control" step="100" value="<?= $f['tva'] ?>"></div>
                <div class="col-md-4 mb-3"><label>Remise (FCFA)</label><input type="number" name="remise" id="remise" class="form-control" step="100" value="<?= $f['remise'] ?>"></div>
                <div class="col-md-6 mb-3"><label>Montant assurance</label><input type="number" name="montant_assurance" class="form-control" step="100" value="<?= $f['montant_assurance'] ?>"></div>
                <div class="col-md-6 mb-3"><div class="form-check mt-4"><input type="checkbox" name="prise_en_charge_assurance" class="form-check-input" <?= $f['prise_en_charge_assurance'] ? 'checked' : '' ?>> <label>Prise en charge assurance</label></div></div>
                <div class="col-md-6 mb-3"><label>Assurance</label><input type="text" name="assurance" class="form-control" value="<?= escape($f['assurance']) ?>"></div>
                <div class="col-md-6 mb-3"><label>Total TTC :</label><h4 id="total_ttc_preview"><?= formatMoney($f['total_ttc']) ?></h4></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
