<?php 
require_once '../../includes/auth.php';
require_once '../../config/database.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getPDO();
    $stmt = $pdo->prepare("INSERT INTO factures (nom_patient, montant) VALUES (?, ?)");
    if ($stmt->execute([$_POST['nom_patient'], $_POST['montant']])) {
        $message = '<div class="alert alert-success">Facture créée avec succès !</div>';
    } else {
        $message = '<div class="alert alert-danger">Erreur lors de la création.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle Facture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="d-flex">
    <div class="sidebar-wrapper" style="width: 250px;">
        <?php include '../../includes/sidebar.php'; ?>
    </div>
    <div class="flex-grow-1 p-4">
        <h2>Créer une nouvelle facture</h2>
        <?= $message ?>
        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label>Nom complet du patient</label>
                <input type="text" name="nom_patient" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Montant (FCFA)</label>
                <input type="number" step="0.01" name="montant" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer la facture
            </button>
            <a href="index.php" class="btn btn-secondary">Retour</a>
        </form>
    </div>
</div>
</body>
</html>
