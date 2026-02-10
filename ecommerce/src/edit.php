<?php
// ==========================
// File: src/edit.php
// ==========================
?>
<?php
$pdo = new PDO("mysql:host=localhost;dbname=ecommerce;charset=utf8", "root", "");
$id = $_GET['id'] ?? null;
$product = ['nom' => '', 'prix' => ''];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id=?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prix = $_POST['prix'];

    if ($id) {
        $stmt = $pdo->prepare("UPDATE produits SET nom=?, prix=? WHERE id=?");
        $stmt->execute([$nom, $prix, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO produits(nom, prix) VALUES(?,?)");
        $stmt->execute([$nom, $prix]);
    }

    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Produit</title>
    <link rel="stylesheet" href="../public/assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2><?= $id ? 'Modifier' : 'Ajouter' ?> un produit</h2>
    <form method="POST">
        <label>Nom du produit</label>
        <input type="text" class="form-control mb-3" name="nom" required value="<?= $product['nom'] ?>">

        <label>Prix</label>
        <input type="number" step="0.01" class="form-control mb-3" name="prix" required value="<?= $product['prix'] ?>">

        <button class="btn btn-success">Enregistrer</button>
        <a href="list.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
</body>
</html>

