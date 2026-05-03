<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$page_title = "Catalogue des Pièces";
include '../../includes/header.php';

$query = "SELECT p.*, f.nom as fournisseur FROM PIECES p 
          LEFT JOIN FOURNISSEURS f ON p.id_fournisseur = f.id_fournisseur ORDER BY p.nom_piece ASC";
$stmt = $db->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fas fa-list text-primary"></i> Référentiel Articles</h3>
    <a href="ajouter_piece.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Pièce</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Réf</th>
                    <th>Désignation</th>
                    <th>Catégorie</th>
                    <th class="text-end">P. Achat</th>
                    <th class="text-end">CUMP</th>
                    <th class="text-end">P. Vente</th>
                    <th class="text-center">Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><code class="fw-bold"><?= $row['reference'] ?></code></td>
                    <td><?= $row['nom_piece'] ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $row['categorie'] ?></span></td>
                    <td class="text-end"><?= number_format($row['prix_achat'], 0, ',', ' ') ?> F</td>
                    <td class="text-end text-info fw-bold"><?= number_format($row['cump'], 0, ',', ' ') ?> F</td>
                    <td class="text-end fw-bold text-success"><?= number_format($row['prix_vente'], 0, ',', ' ') ?> F</td>
                    <td class="text-center">
                        <span class="badge <?= $row['stock_actuel'] <= 5 ? 'bg-danger' : 'bg-success' ?>">
                            <?= $row['stock_actuel'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_piece.php?id=<?= $row['id_piece'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
