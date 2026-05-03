<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facture_id = $_POST['facture_id'] ?? 0;
    $montant = (float)($_POST['montant'] ?? 0);
    $mode = $_POST['mode'] ?? '';
    $reference = trim($_POST['reference'] ?? '');
    $commentaire = trim($_POST['commentaire'] ?? '');
    $encaisse_par_id = currentUserId();

    if (!$facture_id || !$montant || !$mode) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO paiements (facture_id, montant, mode, reference, commentaire, encaisse_par_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$facture_id, $montant, $mode, $reference, $commentaire, $encaisse_par_id]);

            // Mettre à jour le total payé de la facture
            $total_paye = $pdo->prepare("SELECT SUM(montant) FROM paiements WHERE facture_id = ?")->execute([$facture_id])->fetchColumn();
            $facture = $pdo->prepare("SELECT total_ttc FROM factures WHERE id = ?")->execute([$facture_id])->fetch();
            if ($total_paye >= $facture['total_ttc']) {
                $pdo->prepare("UPDATE factures SET reglee = 1, date_reglement = CURDATE() WHERE id = ?")->execute([$facture_id]);
            }
            $pdo->commit();
            $success = "Paiement enregistré.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

$factures = $pdo->query("SELECT f.id, f.numero_facture, CONCAT(u.last_name, ' ', u.first_name) as patient_nom 
                         FROM factures f
                         JOIN patients p ON f.patient_id = p.id
                         JOIN users u ON p.user_id = u.id
                         WHERE f.reglee = 0
                         ORDER BY f.date_emission DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrer un paiement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Enregistrer un paiement</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3"><label>Facture *</label><select name="facture_id" class="form-control" required>
                <option value="">-- Sélectionner une facture impayée --</option>
                <?php foreach ($factures as $f): ?>
                <option value="<?= $f['id'] ?>"><?= escape($f['numero_facture'] . ' - ' . $f['patient_nom']) ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="mb-3"><label>Montant (FCFA) *</label><input type="number" name="montant" class="form-control" step="100" required></div>
            <div class="mb-3"><label>Mode de paiement *</label><select name="mode" class="form-control" required>
                <option value="especes">Espèces</option><option value="carte">Carte bancaire</option><option value="cheque">Chèque</option>
                <option value="virement">Virement</option><option value="assurance">Prise en charge assurance</option><option value="mobile_money">Mobile Money</option>
            </select></div>
            <div class="mb-3"><label>Référence</label><input type="text" name="reference" class="form-control"></div>
            <div class="mb-3"><label>Commentaire</label><textarea name="commentaire" class="form-control" rows="2"></textarea></div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
