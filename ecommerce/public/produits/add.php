<?php
session_start();
require_once __DIR__ . "/../../src/includes/config.php";
require_once __DIR__ . "/../../src/includes/header.php";

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

$error = '';
$success = '';

// Traitement du formulaire
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $quantite = $_POST['quantite'];

    // Upload image
    $image_name = null;
    if(!empty($_FILES['image']['name'])) {
        $target_dir = __DIR__ . "/uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir.$image_name);
    }

    $stmt = $conn->prepare("INSERT INTO produits (code, nom, description, prix, quantite, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdis", $code, $nom, $description, $prix, $quantite, $image_name);

    if($stmt->execute()){
        $success = "Produit ajouté avec succès !";
    } else {
        $error = "Erreur: " . $stmt->error;
    }
}
?>

<div class="container mt-4">
    <h2>Ajouter un Produit</h2>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Code produit</label>
            <input type="text" name="code" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Nom produit</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Prix</label>
            <input type="number" step="0.01" name="prix" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Quantité</label>
            <input type="number" name="quantite" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Image produit</label>
            <input type="file" name="image" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Ajouter Produit</button>
    </form>
</div>

<?php require_once __DIR__ . "/../../src/includes/footer.php"; ?>

