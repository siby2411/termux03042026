<?php
session_start();
require_once __DIR__ . "/../../src/includes/config.php";
require_once __DIR__ . "/../../src/includes/header.php";

// Vérifier la connexion admin
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

// Récupérer les produits
$result = $conn->query("SELECT * FROM produits ORDER BY id DESC");
?>

<h2>Liste des Produits</h2>
<a href="add.php" class="btn btn-success mb-3">Ajouter Produit</a>

<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Quantité</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['code']) ?></td>
            <td><?= htmlspecialchars($row['nom']) ?></td>
            <td><?= $row['prix'] ?></td>
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

