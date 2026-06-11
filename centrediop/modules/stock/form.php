<?php 
require_once '../../includes/auth.php';
require_once '../../config/database.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getPDO();
    $stmt = $pdo->prepare("INSERT INTO stock (nom_produit, quantite, seuil_alerte) VALUES (?, ?, ?)");
    if ($stmt->execute([$_POST['nom_produit'], $_POST['quantite'], $_POST['seuil_alerte']])) {
        $message = '<div class="alert alert-success">Produit ajouté avec succès !</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="d-flex">
    <div class="sidebar-wrapper" style="width: 250px;">
        <?php include '../../includes/sidebar.php'; ?>
    </div>
    <div class="flex-grow-1 p-4">
        <h2>Ajouter un article</h2>
        <?= $message ?>
        <form method="POST" class="card p-4">
            <div class="mb-3">
                <label>Nom du produit</label>
                <input type="text" name="nom_produit" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Quantité initiale</label>
                <input type="number" name="quantite" class="form-control" value="0" required>
            </div>
            <div class="mb-3">
                <label>Seuil d'alerte</label>
                <input type="number" name="seuil_alerte" class="form-control" value="5">
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="index.php" class="btn btn-secondary">Retour</a>
        </form>
    </div>
</div>
</body>
</html>
