<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

// Logique de recherche
$search = $_GET['q'] ?? '';
$sql = "SELECT * FROM PIECES WHERE nom_piece LIKE ? OR reference LIKE ? ORDER BY id_piece DESC";
$stmt = $db->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$pieces = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-search-plus"></i> Liste & Recherche de Pièces</h3>
        <form class="d-flex" method="GET">
            <input type="text" name="q" class="form-control" placeholder="Rechercher par nom ou réf..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover bg-white shadow-sm">
            <thead class="table-dark">
                <tr><th>Référence</th><th>Nom de la pièce</th><th>Prix</th><th>Stock Actuel</th></tr>
            </thead>
            <tbody>
                <?php if(count($pieces) > 0): ?>
                    <?php foreach($pieces as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['reference']) ?></strong></td>
                        <td><?= htmlspecialchars($p['nom_piece']) ?></td>
                        <td><?= number_format($p['prix_vente'], 0, ',', ' ') ?> F</td>
                        <td>
                            <span class="badge bg-<?= $p['stock_actuel'] < 10 ? 'danger' : 'success' ?>">
                                <?= $p['stock_actuel'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Aucune pièce trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
