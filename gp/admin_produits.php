<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $stmt = $pdo->prepare("INSERT INTO produits (nom, description, prix, stock) VALUES (?,?,?,?)");
    $stmt->execute([$_POST['nom'], $_POST['description'], $_POST['prix'], $_POST['stock']]);
    $id = $pdo->lastInsertId();
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = "produit_{$id}.{$ext}";
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $filename);
        $pdo->prepare("UPDATE produits SET image = ? WHERE id = ?")->execute(["uploads/$filename", $id]);
    }
    echo "<div class='alert alert-success'>✅ Produit ajouté</div>";
}

// Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("UPDATE produits SET nom=?, description=?, prix=?, stock=? WHERE id=?");
    $stmt->execute([$_POST['nom'], $_POST['description'], $_POST['prix'], $_POST['stock'], $id]);
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = "produit_{$id}.{$ext}";
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $filename);
        $pdo->prepare("UPDATE produits SET image = ? WHERE id = ?")->execute(["uploads/$filename", $id]);
    }
    echo "<div class='alert alert-success'>✅ Produit modifié</div>";
}

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer') {
    $id = $_POST['id'];
    $pdo->prepare("DELETE FROM produits WHERE id = ?")->execute([$id]);
    echo "<div class='alert alert-warning'>🗑️ Produit supprimé</div>";
}

$produits = $pdo->query("SELECT * FROM produits ORDER BY date_ajout DESC")->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<h2><i class="fas fa-apple-alt"></i> Administration Épicerie</h2>

<!-- Formulaire d'ajout -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">➕ Ajouter un produit</div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="ajouter">
            <div class="row">
                <div class="col-md-4"><input type="text" name="nom" class="form-control mb-2" placeholder="Nom" required></div>
                <div class="col-md-3"><input type="number" step="0.01" name="prix" class="form-control mb-2" placeholder="Prix €" required></div>
                <div class="col-md-2"><input type="number" name="stock" class="form-control mb-2" placeholder="Stock"></div>
                <div class="col-md-3"><input type="file" name="image" class="form-control mb-2" accept="image/*"></div>
            </div>
            <div class="row">
                <div class="col-md-12"><textarea name="description" class="form-control mb-2" rows="2" placeholder="Description"></textarea></div>
            </div>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
        </form>
    </div>
</div>

<h3>📦 Liste des produits</h3>
<?php foreach ($produits as $p): ?>
<div class="card mb-3">
    <div class="card-header bg-warning">✏️ Modifier : <?= htmlspecialchars($p['nom']) ?></div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <div class="row">
                <div class="col-md-2">
                    <?php if (!empty($p['image'])): ?>
                        <img src="<?= $p['image'] ?>" style="max-width:60px; border-radius:10px;">
                    <?php endif; ?>
                </div>
                <div class="col-md-4"><input type="text" name="nom" class="form-control mb-2" value="<?= htmlspecialchars($p['nom']) ?>" required></div>
                <div class="col-md-2"><input type="number" step="0.01" name="prix" class="form-control mb-2" value="<?= $p['prix'] ?>" required></div>
                <div class="col-md-2"><input type="number" name="stock" class="form-control mb-2" value="<?= $p['stock'] ?>"></div>
                <div class="col-md-2"><input type="file" name="image" class="form-control mb-2" accept="image/*"></div>
            </div>
            <div class="row">
                <div class="col-md-12"><textarea name="description" class="form-control mb-2" rows="2"><?= htmlspecialchars($p['description']) ?></textarea></div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-warning">✅ Modifier ce produit</button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-danger" onclick="if(confirm('Supprimer ?')){ var f=document.createElement('form'); f.method='post'; f.innerHTML='<input type=hidden name=action value=supprimer><input type=hidden name=id value=<?= $p['id'] ?>>'; document.body.appendChild(f); f.submit(); }">🗑️ Supprimer</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<?php include('footer.php'); ?>
