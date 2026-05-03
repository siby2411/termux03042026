<?php
include 'includes/db.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ref = $_POST['ref_interne'];
    $des = $_POST['designation'];
    $prix = $_POST['prix_unitaire'];
    $stock = $_POST['stock_actuel'];
    $seuil = $_POST['seuil_alerte'];
    $image_name = 'default_product.png';

    // Traitement de l'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = "prod_" . time() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/produits/" . $image_name);
    }

    $stmt = $pdo->prepare("INSERT INTO produits (ref_interne, designation, prix_unitaire, stock_actuel, seuil_alerte, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$ref, $des, $prix, $stock, $seuil, $image_name])) {
        echo "<div class='alert alert-success'>Produit ajouté avec succès !</div>";
    }
}
?>
<div class="card shadow-sm border-0 col-md-8 mx-auto">
    <div class="card-header bg-dark text-white"><h5>📸 Nouveau Produit avec Visuel</h5></div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Référence</label><input type="text" name="ref_interne" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Désignation</label><input type="text" name="designation" class="form-control" required></div>
            </div>
            <div class="mb-3"><label>Photo du produit</label><input type="file" name="image" class="form-control" accept="image/*"></div>
            <div class="row">
                <div class="col-md-4 mb-3"><label>Prix HT</label><input type="number" step="0.01" name="prix_unitaire" class="form-control" required></div>
                <div class="col-md-4 mb-3"><label>Stock</label><input type="number" name="stock_actuel" class="form-control" value="0"></div>
                <div class="col-md-4 mb-3"><label>Seuil Alerte</label><input type="number" name="seuil_alerte" class="form-control" value="5"></div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enregistrer le produit</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
