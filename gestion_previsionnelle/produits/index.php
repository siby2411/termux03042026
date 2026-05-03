<?php
$page_title = "Gestion des Produits & Stocks";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php'; 

$database = new Database();
$db = $database->getConnection();

$query = "SELECT ProduitID, Nom, Reference, StockActuel, CUMP, PrixVente FROM Produits ORDER BY Nom ASC";
try {
    $stmt = $db->query($query);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur SQL: Impossible de charger les produits. " . $e->getMessage() . "</div>";
    $produits = [];
}
?>

<h1 class="mt-4 text-center"><i class="fas fa-boxes me-2"></i> Liste des Produits & Stocks</h1>
<p class="text-muted text-center">Visualisation du stock actuel, du CUMP et des prix de vente.</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="d-flex justify-content-end mb-3">
            <a href="creer.php" class="btn btn-success"><i class="fas fa-plus me-2"></i> Ajouter un Produit</a>
        </div>

        <div class="card shadow-lg mb-4 border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-custom mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Réf.</th>
                                <th>Nom du Produit</th>
                                <th class="text-center">Stock Actuel</th>
                                <th class="text-end">CUMP (€)</th>
                                <th class="text-end">Prix Vente (€)</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($produits)): ?>
                            <?php foreach ($produits as $prod): ?>
                            <tr>
                                <td><?= htmlspecialchars($prod['Reference']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($prod['Nom']) ?></td>
                                <td class="text-center">
                                    <?php 
                                        $stock_class = ($prod['StockActuel'] < 5) ? 'bg-danger text-white' : 'bg-success text-white';
                                    ?>
                                    <span class="badge <?= $stock_class ?>"><?= htmlspecialchars($prod['StockActuel']) ?></span>
                                </td>
                                <td class="text-end"><?= number_format($prod['CUMP'], 2, ',', ' ') ?></td>
                                <td class="text-end"><?= number_format($prod['PrixVente'], 2, ',', ' ') ?></td>
                                <td class="text-center">
                                    <a href="modifier.php?id=<?= $prod['ProduitID'] ?>" class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="supprimer.php?id=<?= $prod['ProduitID'] ?>" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Attention: Supprimer un produit impacte l\'historique des ventes/achats. Continuer?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted p-4">Aucun produit enregistré.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
