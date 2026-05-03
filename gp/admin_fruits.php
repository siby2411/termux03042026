<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Créer le dossier d'upload
$upload_dir = __DIR__ . '/uploads/fruits/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ajouter') {
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = 'fruit_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "uploads/fruits/" . $filename);
            $image = "uploads/fruits/$filename";
        }
        $stmt = $pdo->prepare("INSERT INTO fruits_tropicaux (nom, description, image_url, categorie, prix, stock, badge) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$_POST['nom'], $_POST['description'], $image, $_POST['categorie'], $_POST['prix'], $_POST['stock'], $_POST['badge']]);
        echo "<div class='alert alert-success'>✅ Produit ajouté</div>";
    }
    
    if ($action === 'modifier') {
        $id = $_POST['id'];
        
        // Récupération des données avec gestion des caractères spéciaux
        $nom = trim($_POST['nom']);
        $description = trim($_POST['description']);
        $categorie = trim($_POST['categorie']);
        $prix = floatval($_POST['prix']);
        $stock = intval($_POST['stock']);
        $badge = trim($_POST['badge']);
        $image = $_POST['image_existante'] ?? '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = 'fruit_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "uploads/fruits/" . $filename);
            $image = "uploads/fruits/$filename";
        }
        
        $stmt = $pdo->prepare("UPDATE fruits_tropicaux SET nom=?, description=?, image_url=?, categorie=?, prix=?, stock=?, badge=? WHERE id=?");
        $stmt->execute([$nom, $description, $image, $categorie, $prix, $stock, $badge, $id]);
        echo "<div class='alert alert-success'>✅ Produit modifié</div>";
    }
    
    if ($action === 'supprimer') {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM fruits_tropicaux WHERE id = ?")->execute([$id]);
        echo "<div class='alert alert-warning'>🗑️ Produit supprimé</div>";
    }
}

$produits = $pdo->query("SELECT * FROM fruits_tropicaux ORDER BY date_ajout DESC")->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .fruit-card { transition: transform 0.2s; border-radius: 15px; overflow: hidden; }
    .fruit-card:hover { transform: translateY(-5px); }
</style>

<h2><i class="fas fa-leaf"></i> Administration Fruits tropicaux</h2>

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <i class="fas fa-plus-circle"></i> <span id="formTitle">Ajouter un produit</span>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="action" id="formAction" value="ajouter">
            <input type="hidden" name="id" id="productId">
            <input type="hidden" name="image_existante" id="imageExistante">
            <div class="row">
                <div class="col-md-4"><input type="text" name="nom" id="nom" class="form-control mb-2" placeholder="Nom du produit" required></div>
                <div class="col-md-3">
                    <select name="categorie" id="categorie" class="form-select mb-2">
                        <option value="mangue">🥭 Mangue</option>
                        <option value="ditakh">🍐 Ditakh (Jujube)</option>
                        <option value="bissap">🌺 Bissap (Hibiscus)</option>
                        <option value="baobab">🌳 Baobab (Bouye)</option>
                        <option value="autre">🍍 Autre</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="number" step="0.01" name="prix" id="prix" class="form-control mb-2" placeholder="Prix (€)"></div>
                <div class="col-md-1"><input type="number" name="stock" id="stock" class="form-control mb-2" placeholder="Stock"></div>
                <div class="col-md-2"><input type="text" name="badge" id="badge" class="form-control mb-2" placeholder="Badge (Exporter, Bio)"></div>
            </div>
            <div class="row">
                <div class="col-md-8"><textarea name="description" id="description" class="form-control mb-2" rows="2" placeholder="Description"></textarea></div>
                <div class="col-md-4"><input type="file" name="image" id="image" class="form-control mb-2" accept="image/*"></div>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
            <button type="button" class="btn btn-secondary" onclick="resetForm()">Annuler</button>
        </form>
    </div>
</div>

<h3>Liste des produits</h3>
<div class="row">
    <?php foreach ($produits as $p): ?>
    <div class="col-md-3 mb-3">
        <div class="card fruit-card h-100">
            <img src="<?= !empty($p['image_url']) ? $p['image_url'] : 'https://placehold.co/300x180/2c5f2d/white?text='.urlencode($p['nom']) ?>" class="card-img-top" style="height:150px; object-fit:cover;">
            <div class="card-body">
                <h6><?= htmlspecialchars($p['nom']) ?></h6>
                <span class="badge bg-secondary"><?= $p['categorie'] ?></span>
                <?php if ($p['badge']): ?>
                    <span class="badge bg-success"><?= htmlspecialchars($p['badge']) ?></span>
                <?php endif; ?>
                <p class="mt-2 fw-bold"><?= number_format($p['prix'], 2) ?> €</p>
                <button class="btn btn-sm btn-warning" onclick='loadProductForEdit(<?= $p['id'] ?>)'>✏️ Modifier</button>
                <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $p['id'] ?>)">🗑️ Supprimer</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
// Fonction pour charger les données via AJAX (solution robuste)
function loadProductForEdit(id) {
    // Afficher un indicateur de chargement
    const originalBtnText = document.querySelector('.btn-warning')?.innerHTML;
    
    fetch(`get_fruit.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const p = data.product;
                document.getElementById('formAction').value = 'modifier';
                document.getElementById('productId').value = p.id;
                document.getElementById('nom').value = p.nom;
                document.getElementById('description').value = p.description;
                document.getElementById('categorie').value = p.categorie;
                document.getElementById('prix').value = p.prix;
                document.getElementById('stock').value = p.stock;
                document.getElementById('badge').value = p.badge;
                document.getElementById('imageExistante').value = p.image_url;
                document.getElementById('formTitle').innerHTML = '✏️ Modifier le produit';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                alert('Erreur: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Impossible de charger les données du produit');
        });
}

function resetForm() {
    document.getElementById('productForm').reset();
    document.getElementById('formAction').value = 'ajouter';
    document.getElementById('productId').value = '';
    document.getElementById('imageExistante').value = '';
    document.getElementById('formTitle').innerHTML = 'Ajouter un produit';
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
