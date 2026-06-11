<?php 
require_once '../../includes/auth.php';
require_once '../../config/database.php';
$pdo = getPDO();
$produits = $pdo->query("SELECT * FROM stock ORDER BY date_ajout DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion du Stock</title>
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
        <h2>Gestion du Stock</h2>
        <table class="table table-striped mt-4">
            <thead><tr><th>Produit</th><th>Quantité</th><th>Seuil</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($produits as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nom_produit']) ?></td>
                    <td><?= $p['quantite'] ?></td>
                    <td><?= $p['seuil_alerte'] ?></td>
                    <td><button class="btn btn-sm btn-info"><i class="fas fa-edit"></i></button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
