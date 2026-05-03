<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$pdo = getPDO();
$produits = $pdo->query("SELECT p.*, c.nom as categorie_nom, f.nom as fournisseur_nom 
                         FROM produits p
                         LEFT JOIN categories c ON p.categorie_id = c.id
                         LEFT JOIN fournisseurs f ON p.fournisseur_principal = f.id
                         ORDER BY p.nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Produits</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Liste des produits</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau produit</a>
        <table class="table table-striped">
            <thead><tr><th>Code</th><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Seuil</th><th>Fournisseur</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($produits as $p): ?>
                <tr>
                    <td><?= escape($p['code']) ?></td>
                    <td><?= escape($p['nom']) ?></td>
                    <td><?= escape($p['categorie_nom']) ?></td>
                    <td><?= formatMoney($p['prix_vente']) ?></td>
                    <td class="<?= $p['quantite_stock'] <= $p['seuil_alerte'] ? 'text-danger fw-bold' : '' ?>"><?= $p['quantite_stock'] ?></td>
                    <td><?= $p['seuil_alerte'] ?></td>
                    <td><?= escape($p['fournisseur_nom']) ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
