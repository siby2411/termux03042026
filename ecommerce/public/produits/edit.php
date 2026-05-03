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

// Vérifier que l'id est présent
if(!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID produit manquant");
}

$id = intval($_GET['id']);

// Récupérer les données du produit
$stmt = $conn->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows !== 1){
    die("Produit introuvable");
}
$product = $result->fetch_assoc();

// Traitement du formulaire
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $code = $_POST['code'];
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $quantite = $_POST['quantite'];

    // Upload image
    $image_name = $product['image'];
    if(!empty($_FILES['image']['name'])){
        $target_dir = __DIR__ . "/uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir.$image_name);
    }

    $stmt = $conn->prepare("UPDATE produits SET code=?, nom=?, description=?, prix=?, quantite=?, image=? WHERE id=?");
    $stmt->bind_param("sssdisi", $code, $nom, $description, $prix, $quantite, $image_name, $id);

    if($stmt->execute()){
        $success = "Produit mis à jour avec succès !";
        $product = array_merge($product, $_POST);
        $product['image'] = $image_name;
    } else {
        $error = "Erreur: " . $stmt->error;
    }
}
?>

<div class="container mt-4">
    <h2>Modifier Produit</h2>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Code produit</label>
            <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($product['code']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Nom produit</label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($product['nom']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Prix</label>
            <input type="number" step="0.01" name="prix" class="form-control" value="<?= $product['prix'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Quantité</label>
            <input type="number" name="quantite" class="form-control" value="<?= $product['quantite'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Image produit</label>
            <input type="file" name="image" class="form-control">
            <?php if($product['image']): ?>
                <img src="uploads/<?= $product['image'] ?>" alt="Produit" class="img-thumbnail mt-2" width="150">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php require_once __DIR__ . "/../../src/includes/footer.php"; ?>

