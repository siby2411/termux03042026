<?php
session_start();
require_once __DIR__ . "/../../src/includes/config.php";
require_once __DIR__ . "/../../src/includes/header.php";

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

// Récupérer les produits
$result = $conn->query("SELECT * FROM produits ORDER BY id DESC");
?>

<h2>Liste des Produits</h2>
<a href="add.php" class="btn btn-success mb-3">Ajouter Produit</a>

<table class="table table-striped table-bordered align-middle">
    <thead class="table-dark text-center">
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Code</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Quantité</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr class="text-center">
            <td><?= $row['id'] ?></td>
            <td>
                <?php if($row['image']): ?>
                    <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="Produit" class="img-thumbnail" width="80">
                <?php else: ?>
                    <span class="text-muted">Pas d'image</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['code']) ?></td>
            <td><?= htmlspecialchars($row['nom']) ?></td>
            <td><?= number_format($row['prix'], 2) ?> €</td>
            <td><?= $row['quantite'] ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce produit ?')">Supprimer</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php require_once __DIR__ . "/../../src/includes/footer.php"; ?>

