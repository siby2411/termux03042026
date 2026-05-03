<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'Liste des livres';

// Récupérer les livres
$stmt = $pdo->query("
    SELECT l.*, c.nom as categorie_nom 
    FROM livres l
    LEFT JOIN categories c ON l.categorie_id = c.id
    ORDER BY l.titre
");
$livres = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-book"></i> Catalogue des livres</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <a href="ajouter.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un livre
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="table_livres">
                        <thead>
                            <tr>
                                <th>ISBN</th>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>Catégorie</th>
                                <th>Prix vente</th>
                                <th>Stock</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($livres as $livre): ?>
                            <tr>
                                <td><?php echo $livre['isbn'] ?? '-'; ?></td>
                                <td><?php echo htmlspecialchars($livre['titre']); ?></td>
                                <td><?php echo htmlspecialchars($livre['auteur']); ?></td>
                                <td><?php echo $livre['categorie_nom'] ?? '-'; ?></td>
                                <td><?php echo number_format($livre['prix_vente'], 0, ',', ' '); ?> FCFA</td>
                                <td>
                                    <span class="badge bg-<?php echo $livre['quantite_stock'] <= $livre['quantite_min'] ? 'danger' : 'success'; ?>">
                                        <?php echo $livre['quantite_stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $livre['statut'] == 'disponible' ? 'success' : 'warning'; ?>">
                                        <?php echo $livre['statut']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="modifier.php?id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="supprimerLivre(<?php echo $livre['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function supprimerLivre(id) {
    if(confirm('Êtes-vous sûr de vouloir supprimer ce livre ?')) {
        window.location.href = 'supprimer.php?id=' + id;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
