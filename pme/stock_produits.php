<?php
include 'includes/db.php';
include 'includes/header.php';

// Récupération des produits depuis la table 'produits'
try {
    $stmt = $pdo->query("SELECT * FROM produits ORDER BY designation ASC");
    $produits = $stmt->fetchAll();
} catch (PDOException $e) {
    $produits = [];
    echo "<div class='alert alert-danger'>Erreur table produits : " . $e->getMessage() . "</div>";
}
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-boxes text-success"></i> Gestion du Stock</h1>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus-circle"></i> Ajouter un Produit
    </button>
</div>

<div class="row">
    <?php if(count($produits) > 0): ?>
        <?php foreach($produits as $p): ?>
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-light text-dark border">#<?= $p['id_produit'] ?></span>
                        <span class="badge <?= $p['stock_actuel'] > 5 ? 'bg-success' : 'bg-danger' ?>">
                            Stock: <?= $p['stock_actuel'] ?>
                        </span>
                    </div>
                    <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($p['designation']) ?></h5>
                    <h4 class="text-primary mt-3"><?= number_format($p['prix_unitaire'], 2, ',', ' ') ?> $</h4>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-between">
                    <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-history"></i> Mouvement</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center p-5">
            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
            <p class="text-muted">Votre catalogue est vide. Ajoutez votre premier article !</p>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="traitement_produit.php" method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Nouveau Produit OMEGA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Désignation de l'article</label>
                        <input type="text" name="designation" class="form-control" required placeholder="Ex: Ordinateur Dell Latitude">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prix Unitaire ($)</label>
                            <input type="number" step="0.01" name="prix_unitaire" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Initial</label>
                            <input type="number" name="stock_actuel" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Enregistrer en Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
