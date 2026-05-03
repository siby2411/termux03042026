<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';

$dbObj = new Database();
$pdo = $dbObj->getConnection();

// Récupération des pièces avec une jointure ou calcul simple pour l'exemple
$query = $pdo->query("SELECT * FROM pieces_detachees ORDER BY stock_actuel ASC");
$pieces = $query->fetchAll();
?>

<?php if (isset($_GET['warning']) && $_GET['warning'] == 'stock_bas'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-5 border-danger" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> 
        <strong>ALERTE STOCK :</strong> La pièce <strong><?= htmlspecialchars($_GET['item']) ?></strong> a atteint son seuil critique ! Pensez à commander.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'vente_reussie'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-5 border-success" role="alert">
        <i class="fas fa-check-circle me-2"></i> 
        <strong>SUCCÈS :</strong> La vente a été enregistrée et le stock mis à jour.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-boxes text-primary"></i> Gestion du Stock & Magasin</h2>
    <div>
        <a href="formulaire_vente.php" class="btn btn-success shadow-sm me-2"><i class="fas fa-shopping-cart"></i> Nouvelle Vente</a>
        <a href="formulaire_piece.php" class="btn btn-dark shadow-sm"><i class="fas fa-plus"></i> Nouvelle Référence</a>
    </div>
</div>

<div class="card shadow border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Référence</th>
                    <th>Désignation</th>
                    <th>Prix Vente</th>
                    <th class="text-center">Stock Actuel</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pieces as $p): ?>
                <?php $isLow = ($p['stock_actuel'] <= $p['seuil_alerte']); ?>
                <tr class="<?= $isLow ? 'table-warning' : '' ?>">
                    <td class="fw-bold"><?= htmlspecialchars($p['reference']) ?></td>
                    <td><?= htmlspecialchars($p['libelle']) ?></td>
                    <td><?= number_format($p['prix_vente'], 0, ',', ' ') ?> FCFA</td>
                    <td class="text-center">
                        <span class="badge <?= $isLow ? 'bg-danger' : 'bg-success' ?> rounded-pill px-3">
                            <?= $p['stock_actuel'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($isLow): ?>
                            <span class="text-danger small fw-bold"><i class="fas fa-arrow-down"></i> Réapprovisionner</span>
                        <?php else: ?>
                            <span class="text-success small fw-bold"><i class="fas fa-check"></i> Disponible</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="formulaire_piece.php?id=<?= $p['id_piece'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($pieces)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">Aucune pièce en stock.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
