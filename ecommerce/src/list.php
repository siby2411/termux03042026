<?php
// ==========================
// File: src/list.php
// ==========================
?>
<?php
$pdo = new PDO("mysql:host=localhost;dbname=ecommerce;charset=utf8", "root", "");
$products = $pdo->query("SELECT * FROM produits")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des produits</title>
    <link rel="stylesheet" href="../public/assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Liste des produits</h2>
    <a href="edit.php" class="btn btn-success mb-3">Ajouter un produit</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['nom'] ?></td>
                <td><?= $p['prix'] ?> F CFA</td>
                <td>
                    <a href="edit.php?id=<?= $p['id'] }" class="btn btn-warning btn-sm">Modifier</a>
                    <a href="delete.php?id=<?= $p['id'] }" class="btn btn-danger btn-sm">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

