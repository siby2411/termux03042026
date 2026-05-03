<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ajouter') {
        $stmt = $pdo->prepare("INSERT INTO bijouterie (nom, categorie, matiere, description, prix_achat, prix_vente, stock, poids_gramme, pierres, certificat) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$_POST['nom'], $_POST['categorie'], $_POST['matiere'], $_POST['description'], $_POST['prix_achat'], $_POST['prix_vente'], $_POST['stock'], $_POST['poids_gramme'], $_POST['pierres'], isset($_POST['certificat']) ? 1 : 0]);
        $id = $pdo->lastInsertId();
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = "bijou_{$id}.{$ext}";
            move_uploaded_file($_FILES['image']['tmp_name'], "uploads/bijoux/" . $filename);
            $pdo->prepare("UPDATE bijouterie SET image = ? WHERE id = ?")->execute(["uploads/bijoux/$filename", $id]);
        }
        echo "<div class='alert alert-success'>✅ Bijou ajouté</div>";
    }
    
    if ($action === 'modifier') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE bijouterie SET nom=?, categorie=?, matiere=?, description=?, prix_achat=?, prix_vente=?, stock=?, poids_gramme=?, pierres=?, certificat=? WHERE id=?");
        $stmt->execute([$_POST['nom'], $_POST['categorie'], $_POST['matiere'], $_POST['description'], $_POST['prix_achat'], $_POST['prix_vente'], $_POST['stock'], $_POST['poids_gramme'], $_POST['pierres'], isset($_POST['certificat']) ? 1 : 0, $id]);
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = "bijou_{$id}.{$ext}";
            move_uploaded_file($_FILES['image']['tmp_name'], "uploads/bijoux/" . $filename);
            $pdo->prepare("UPDATE bijouterie SET image = ? WHERE id = ?")->execute(["uploads/bijoux/$filename", $id]);
        }
        echo "<div class='alert alert-success'>✅ Bijou modifié</div>";
    }
    
    if ($action === 'supprimer') {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM bijouterie WHERE id = ?")->execute([$id]);
        echo "<div class='alert alert-warning'>🗑️ Bijou supprimé</div>";
    }
}

$bijoux = $pdo->query("SELECT * FROM bijouterie ORDER BY date_ajout DESC")->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .bijou-card { transition: transform 0.2s, box-shadow 0.2s; border-radius: 15px; overflow: hidden; }
    .bijou-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
    .bijou-img { height: 180px; object-fit: cover; width: 100%; }
</style>

<h2><i class="fas fa-gem"></i> Administration Joaillerie - Dieynaba GP Holding</h2>

<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <i class="fas fa-plus-circle"></i> Ajouter / Modifier un bijou
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="action" id="formAction" value="ajouter">
            <input type="hidden" name="id" id="productId">
            <div class="row">
                <div class="col-md-4"><input type="text" name="nom" id="nom" class="form-control mb-2" placeholder="Nom du bijou" required></div>
                <div class="col-md-2">
                    <select name="categorie" id="categorie" class="form-select mb-2">
                        <option value="bague">💍 Bague</option>
                        <option value="collier">📿 Collier</option>
                        <option value="boucle_oreille">💎 Boucle d'oreille</option>
                        <option value="bracelet">📿 Bracelet</option>
                        <option value="montre">⌚ Montre</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="matiere" id="matiere" class="form-select mb-2">
                        <option value="or">🥇 Or</option>
                        <option value="argent">🥈 Argent</option>
                        <option value="platine">💠 Platine</option>
                        <option value="diamant">💎 Diamant</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="number" step="0.01" name="prix_achat" id="prix_achat" class="form-control mb-2" placeholder="Prix achat €"></div>
                <div class="col-md-2"><input type="number" step="0.01" name="prix_vente" id="prix_vente" class="form-control mb-2" placeholder="Prix vente €"></div>
            </div>
            <div class="row">
                <div class="col-md-2"><input type="number" name="stock" id="stock" class="form-control mb-2" placeholder="Stock"></div>
                <div class="col-md-2"><input type="number" step="0.01" name="poids_gramme" id="poids_gramme" class="form-control mb-2" placeholder="Poids (g)"></div>
                <div class="col-md-3"><input type="text" name="pierres" id="pierres" class="form-control mb-2" placeholder="Pierres (ex: Diamant 0.50ct)"></div>
                <div class="col-md-2"><label><input type="checkbox" name="certificat" id="certificat"> 📜 Certificat</label></div>
                <div class="col-md-3"><input type="file" name="image" id="image" class="form-control mb-2" accept="image/*"></div>
            </div>
            <div class="row">
                <div class="col-md-12"><textarea name="description" id="description" class="form-control mb-2" rows="2" placeholder="Description"></textarea></div>
            </div>
            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Enregistrer</button>
            <button type="button" class="btn btn-secondary" onclick="resetForm()">Annuler</button>
        </form>
    </div>
</div>

<h3><i class="fas fa-images"></i> Galerie Joaillerie</h3>
<div class="row">
    <?php foreach ($bijoux as $b): ?>
    <div class="col-md-3 mb-3">
        <div class="card bijou-card h-100">
            <img src="<?= !empty($b['image']) ? $b['image'] : 'https://placehold.co/300x180?text=Bijou' ?>" class="bijou-img" alt="<?= htmlspecialchars($b['nom']) ?>">
            <div class="card-body">
                <h6 class="card-title"><?= htmlspecialchars($b['nom']) ?></h6>
                <small class="text-muted">Code: <?= $b['code_produit'] ?></small><br>
                <span class="badge bg-warning"><?= $b['categorie'] ?></span>
                <span class="badge bg-info"><?= $b['matiere'] ?></span>
                <p class="mt-2"><strong><?= number_format($b['prix_vente'], 2) ?> €</strong></p>
                <button class="btn btn-sm btn-warning" onclick="editBijou(<?= $b['id'] ?>, '<?= addslashes($b['nom']) ?>', '<?= $b['categorie'] ?>', '<?= $b['matiere'] ?>', <?= $b['prix_achat'] ?>, <?= $b['prix_vente'] ?>, <?= $b['stock'] ?>, <?= $b['poids_gramme'] ?>, '<?= addslashes($b['pierres']) ?>', <?= $b['certificat'] ?>, '<?= addslashes($b['description']) ?>')">✏️ Modifier</button>
                <button class="btn btn-sm btn-danger" onclick="deleteBijou(<?= $b['id'] ?>)">🗑️ Supprimer</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function editBijou(id, nom, categorie, matiere, prix_achat, prix_vente, stock, poids, pierres, certificat, desc) {
    document.getElementById('formAction').value = 'modifier';
    document.getElementById('productId').value = id;
    document.getElementById('nom').value = nom;
    document.getElementById('categorie').value = categorie;
    document.getElementById('matiere').value = matiere;
    document.getElementById('prix_achat').value = prix_achat;
    document.getElementById('prix_vente').value = prix_vente;
    document.getElementById('stock').value = stock;
    document.getElementById('poids_gramme').value = poids;
    document.getElementById('pierres').value = pierres;
    document.getElementById('certificat').checked = certificat == 1;
    document.getElementById('description').value = desc;
    document.querySelector('.card-header').innerHTML = '<i class="fas fa-edit"></i> Modifier le bijou';
    window.scrollTo(0, 0);
}
function resetForm() {
    document.getElementById('productForm').reset();
    document.getElementById('formAction').value = 'ajouter';
    document.getElementById('productId').value = '';
    document.querySelector('.card-header').innerHTML = '<i class="fas fa-plus-circle"></i> Ajouter / Modifier un bijou';
}
function deleteBijou(id) {
    if(confirm('Supprimer ce bijou définitivement ?')) {
        let form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="'+id+'">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php include('footer.php'); ?>
