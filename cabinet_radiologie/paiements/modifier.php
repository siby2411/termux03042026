<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) { die("Paiement introuvable."); }

$modes = ['especes', 'carte', 'cheque', 'virement', 'assurance'];
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = (float)($_POST['montant'] ?? 0);
    $mode = $_POST['mode'] ?? '';
    $reference = trim($_POST['reference'] ?? '');
    $commentaire = trim($_POST['commentaire'] ?? '');

    if (!$montant || !$mode) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE paiements SET montant=?, mode=?, reference=?, commentaire=? WHERE id=?");
            $stmt->execute([$montant, $mode, $reference, $commentaire, $id]);

            // Recalculer le statut de la facture
            $total_paye = $pdo->prepare("SELECT SUM(montant) FROM paiements WHERE facture_id = ?")->execute([$p['facture_id']])->fetchColumn();
            $facture = $pdo->prepare("SELECT total_ttc FROM factures WHERE id = ?")->execute([$p['facture_id']])->fetch();
            if ($total_paye >= $facture['total_ttc']) {
                $pdo->prepare("UPDATE factures SET reglee = 1, date_reglement = CURDATE() WHERE id = ?")->execute([$p['facture_id']]);
            } else {
                $pdo->prepare("UPDATE factures SET reglee = 0, date_reglement = NULL WHERE id = ?")->execute([$p['facture_id']]);
            }
            $pdo->commit();
            $success = "Paiement modifié.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Modifier paiement</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier paiement</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3"><label>Montant (FCFA) *</label><input type="number" name="montant" class="form-control" step="100" value="<?= $p['montant'] ?>" required></div>
            <div class="mb-3"><label>Mode *</label><select name="mode" class="form-control" required>
                <?php foreach ($modes as $m): ?>
                <option value="<?= $m ?>" <?= $p['mode'] == $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="mb-3"><label>Référence</label><input type="text" name="reference" class="form-control" value="<?= escape($p['reference']) ?>"></div>
            <div class="mb-3"><label>Commentaire</label><textarea name="commentaire" class="form-control" rows="2"><?= escape($p['commentaire']) ?></textarea></div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
