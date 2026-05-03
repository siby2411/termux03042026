<?php
include 'includes/db.php';
include 'includes/header.php';

// Récupération des produits
$stmt = $pdo->query("SELECT * FROM produits ORDER BY designation ASC");
$produits = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Logistique & Stocks</h1>
    <button class="btn btn-sm btn-success"><i class="fas fa-box-open"></i> Ajouter Produit</button>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered">
            <thead class="bg-success text-white">
                <tr>
                    <th>Réf</th>
                    <th>Désignation</th>
                    <th>Prix Unitaire</th>
                    <th>Stock Actuel</th>
                    <th>Seuil Alerte</th>
                    <th>État</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($produits as $prod): ?>
                <?php 
                    // Logique d'alerte stock
                    $classAlert = ($prod['stock_actuel'] <= $prod['seuil_alerte']) ? 'table-danger' : ''; 
                ?>
                <tr class="<?= $classAlert ?>">
                    <td><strong><?= htmlspecialchars($prod['ref_interne']) ?></strong></td>
                    <td><?= htmlspecialchars($prod['designation']) ?></td>
                    <td><?= $prod['prix_unitaire'] ?> €</td>
                    <td><?= $prod['stock_actuel'] ?></td>
                    <td><?= $prod['seuil_alerte'] ?></td>
                    <td>
                        <?php if($prod['stock_actuel'] <= $prod['seuil_alerte']): ?>
                            <span class="badge bg-danger">Stock Critique !</span>
                        <?php else: ?>
                            <span class="badge bg-success">OK</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
