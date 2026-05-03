<?php
session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$conn = db_connect();

// --- LOGIQUE D'AFFICHAGE (READ) ---
$produits = [];
$result = $conn->query("SELECT id_produit, code_produit, designation, prix_unitaire, stock_actuel FROM produits ORDER BY id_produit DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produits[] = $row;
    }
}

$conn->close();

// Message de succès/erreur après une action CUD
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// 1. INCLUSION DU HEADER (Remplace <!DOCTYPE html>... <head>... <body>)
include 'header.php';
?>

<h1 class="mb-4 text-primary">📦 Gestion des Produits (CRUD)</h1>
<p><a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour au Tableau de Bord</a></p>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

---

<div class="card shadow mb-5">
    <div class="card-header bg-primary text-white">
        <h2 class="h5 mb-0">Ajouter un Nouveau Produit</h2>
    </div>
    <div class="card-body">
        <form action="traitement_produit.php" method="post">
            <input type="hidden" name="action" value="ajouter">
            
            <div class="row g-3">
                
                <div class="col-md-6">
                    <label for="designation" class="form-label">Désignation:</label>
                    <input type="text" class="form-control" name="designation" required>
                </div>
                
                <div class="col-md-3">
                    <label for="prix_unitaire" class="form-label">Prix Unitaire (DZD):</label>
                    <input type="number" class="form-control" name="prix_unitaire" step="0.01" required>
                </div>
                
                <div class="col-md-3">
                    <label for="stock_initial" class="form-label">Stock Initial (Optionnel):</label>
                    <input type="number" class="form-control" name="stock_initial" value="0">
                </div>
                
            </div>
            
            <button type="submit" class="btn btn-success mt-4">Ajouter Produit</button>
        </form>
    </div>
</div>

---

<h2 class="mb-3">Liste des Produits <span class="badge bg-secondary"><?php echo count($produits); ?></span></h2>

<?php if (count($produits) > 0): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Désignation</th>
                <th class="text-end">Prix Unitaire</th>
                <th class="text-center">Stock Actuel</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $p): ?>
            <tr>
                <td><?php echo $p['id_produit']; ?></td>
                <td><?php echo htmlspecialchars($p['code_produit']); ?></td>
                <td><?php echo htmlspecialchars($p['designation']); ?></td>
                <td class="text-end fw-bold"><?php echo number_format($p['prix_unitaire'], 2, ',', ' '); ?> DZD</td>
                <td class="text-center">
                    <?php 
                        $stock = (int)$p['stock_actuel'];
                        if ($stock <= 5 && $stock > 0) {
                            echo '<span class="badge bg-warning text-dark">' . $stock . ' (Faible)</span>';
                        } elseif ($stock <= 0) {
                            echo '<span class="badge bg-danger">' . $stock . ' (Rupture)</span>';
                        } else {
                            echo '<span class="badge bg-success">' . $stock . '</span>';
                        }
                    ?>
                </td>
                <td class="text-center">
                    <a href="modifier_produit.php?id=<?php echo $p['id_produit']; ?>" class="btn btn-sm btn-outline-primary me-2">Modifier</a>
                    <a href="traitement_produit.php?action=supprimer&id=<?php echo $p['id_produit']; ?>" 
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer le produit <?php echo htmlspecialchars($p['designation']); ?> ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading">Aucun Produit!</h4>
        <p>Veuillez utiliser le formulaire ci-dessus pour ajouter votre premier produit à l'inventaire.</p>
    </div>
<?php endif; ?>

<?php
// 2. INCLUSION DU FOOTER (Remplace </body> </html>)
include 'footer.php';
?>
