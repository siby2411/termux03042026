<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Créer le dossier pour les images
$upload_dir = __DIR__ . '/uploads/mode/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ajouter') {
        $stmt = $pdo->prepare("INSERT INTO mode_accessoires (nom, categorie, genre, marque, matiere, description, prix_achat, prix_vente, stock, tailles_disponibles, couleurs) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$_POST['nom'], $_POST['categorie'], $_POST['genre'], $_POST['marque'], $_POST['matiere'], $_POST['description'], $_POST['prix_achat'], $_POST['prix_vente'], $_POST['stock'], $_POST['tailles_disponibles'], $_POST['couleurs']]);
        $id = $pdo->lastInsertId();
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = "mode_{$id}.{$ext}";
            move_uploaded_file($_FILES['image']['tmp_name'], "uploads/mode/" . $filename);
            $pdo->prepare("UPDATE mode_accessoires SET image = ? WHERE id = ?")->execute(["uploads/mode/$filename", $id]);
        }
        echo "<div class='alert alert-success'>✅ Produit ajouté</div>";
    }
    
    if ($action === 'modifier') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE mode_accessoires SET nom=?, categorie=?, genre=?, marque=?, matiere=?, description=?, prix_achat=?, prix_vente=?, stock=?, tailles_disponibles=?, couleurs=? WHERE id=?");
        $stmt->execute([$_POST['nom'], $_POST['categorie'], $_POST['genre'], $_POST['marque'], $_POST['matiere'], $_POST['description'], $_POST['prix_achat'], $_POST['prix_vente'], $_POST['stock'], $_POST['tailles_disponibles'], $_POST['couleurs'], $id]);
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = "mode_{$id}.{$ext}";
            move_uploaded_file($_FILES['image']['tmp_name'], "uploads/mode/" . $filename);
            $pdo->prepare("UPDATE mode_accessoires SET image = ? WHERE id = ?")->execute(["uploads/mode/$filename", $id]);
        }
        echo "<div class='alert alert-success'>✅ Produit modifié</div>";
    }
    
    if ($action === 'supprimer') {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM mode_accessoires WHERE id = ?")->execute([$id]);
        echo "<div class='alert alert-warning'>🗑️ Produit supprimé</div>";
    }
}

$produits = $pdo->query("SELECT * FROM mode_accessoires ORDER BY date_ajout DESC")->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .mode-card { transition: transform 0.2s; border-radius: 15px; overflow: hidden; }
    .mode-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
    .mode-img { height: 200px; object-fit: cover; width: 100%; }
</style>

<h2><i class="fas fa-shoe-prints"></i> Administration Mode & Accessoires - Dieynaba GP Holding</h2>
<p class="text-muted">Chaussures, Sacs, Accessoires de luxe, Parfums</p>

<div class="card mb-4">
    <div class="card-header" style="background: #8B4513; color: white;">
        <i class="fas fa-plus-circle"></i> Ajouter / Modifier un produit
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="action" id="formAction" value="ajouter">
            <input type="hidden" name="id" id="productId">
            <div class="row">
                <div class="col-md-3"><input type="text" name="nom" id="nom" class="form-control mb-2" placeholder="Nom du produit" required></div>
                <div class="col-md-2">
                    <select name="categorie" id="categorie" class="form-select mb-2">
                        <option value="chaussures">👠 Chaussures</option>
                        <option value="sacs">👜 Sacs</option>
                        <option value="accessoires">💍 Accessoires</option>
                        <option value="ceintures">🔗 Ceintures</option>
                        <option value="montres">⌚ Montres</option>
                        <option value="parfums">🌸 Parfums</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="genre" id="genre" class="form-select mb-2">
                        <option value="femme">👩 Femme</option>
                        <option value="homme">👨 Homme</option>
                        <option value="unisexe">🔄 Unisexe</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="text" name="marque" id="marque" class="form-control mb-2" placeholder="Marque"></div>
                <div class="col-md-3"><input type="text" name="matiere" id="matiere" class="form-control mb-2" placeholder="Matière"></div>
            </div>
            <div class="row">
                <div class="col-md-3"><input type="number" step="0.01" name="prix_achat" id="prix_achat" class="form-control mb-2" placeholder="Prix achat €"></div>
                <div class="col-md-3"><input type="number" step="0.01" name="prix_vente" id="prix_vente" class="form-control mb-2" placeholder="Prix vente €"></div>
                <div class="col-md-2"><input type="number" name="stock" id="stock" class="form-control mb-2" placeholder="Stock"></div>
                <div class="col-md-2"><input type="text" name="tailles_disponibles" id="tailles" class="form-control mb-2" placeholder="Tailles (ex: 36,37,38)"></div>
                <div class="col-md-2"><input type="text" name="couleurs" id="couleurs" class="form-control mb-2" placeholder="Couleurs"></div>
            </div>
            <div class="row">
                <div class="col-md-8"><textarea name="description" id="description" class="form-control mb-2" rows="2" placeholder="Description"></textarea></div>
                <div class="col-md-4"><input type="file" name="image" id="image" class="form-control mb-2" accept="image/*"></div>
            </div>
            <button type="submit" class="btn" style="background: #8B4513; color:white;"><i class="fas fa-save"></i> Enregistrer</button>
            <button type="button" class="btn btn-secondary" onclick="resetForm()">Annuler</button>
        </form>
    </div>
</div>

<h3><i class="fas fa-images"></i> Galerie Mode & Accessoires</h3>
<div class="row">
    <?php foreach ($produits as $p): ?>
    <div class="col-md-3 mb-3">
        <div class="card mode-card h-100">
            <img src="<?= !empty($p['image']) ? $p['image'] : 'https://placehold.co/300x200/8B4513/white?text=Produit' ?>" class="mode-img" alt="<?= htmlspecialchars($p['nom']) ?>">
            <div class="card-body">
                <h6 class="card-title"><?= htmlspecialchars($p['nom']) ?></h6>
                <small class="text-muted">Code: <?= $p['code_produit'] ?></small><br>
                <span class="badge bg-secondary"><?= $p['categorie'] ?></span>
                <span class="badge" style="background:#8B4513;"><?= $p['genre'] ?></span>
                <p class="mt-2"><strong><?= number_format($p['prix_vente'], 2) ?> €</strong></p>
                <button class="btn btn-sm btn-warning" onclick='editProduct(<?= json_encode($p) ?>)'>✏️ Modifier</button>
                <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $p['id'] ?>)">🗑️ Supprimer</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function editProduct(p) {
    document.getElementById('formAction').value = 'modifier';
    document.getElementById('productId').value = p.id;
    document.getElementById('nom').value = p.nom;
    document.getElementById('categorie').value = p.categorie;
    document.getElementById('genre').value = p.genre;
    document.getElementById('marque').value = p.marque || '';
    document.getElementById('matiere').value = p.matiere || '';
    document.getElementById('prix_achat').value = p.prix_achat;
    document.getElementById('prix_vente').value = p.prix_vente;
    document.getElementById('stock').value = p.stock;
    document.getElementById('tailles').value = p.tailles_disponibles || '';
    document.getElementById('couleurs').value = p.couleurs || '';
    document.getElementById('description').value = p.description || '';
    document.querySelector('.card-header').innerHTML = '<i class="fas fa-edit"></i> Modifier le produit';
    window.scrollTo(0, 0);
}
function resetForm() {
    document.getElementById('productForm').reset();
    document.getElementById('formAction').value = 'ajouter';
    document.getElementById('productId').value = '';
    document.querySelector('.card-header').innerHTML = '<i class="fas fa-plus-circle"></i> Ajouter / Modifier un produit';
}
function deleteProduct(id) {
    if(confirm('Supprimer ce produit définitivement ?')) {
        let form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="'+id+'">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php include('footer.php'); ?>
