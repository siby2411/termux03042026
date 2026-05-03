<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Ajout d'un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $stmt = $pdo->prepare("INSERT INTO negoce (nom, categorie, marque, modele, description, prix_achat, prix_vente, stock, garantie_mois, etat) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$_POST['nom'], $_POST['categorie'], $_POST['marque'], $_POST['modele'], $_POST['description'], $_POST['prix_achat'], $_POST['prix_vente'], $_POST['stock'], $_POST['garantie_mois'], $_POST['etat']]);
    $id = $pdo->lastInsertId();
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = "negoce_{$id}.{$ext}";
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/negoce/" . $filename);
        $pdo->prepare("UPDATE negoce SET image = ? WHERE id = ?")->execute(["uploads/negoce/$filename", $id]);
    }
    echo "<div class='alert alert-success'>✅ Produit ajouté</div>";
}

// Modification d'un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("UPDATE negoce SET nom=?, categorie=?, marque=?, modele=?, description=?, prix_achat=?, prix_vente=?, stock=?, garantie_mois=?, etat=? WHERE id=?");
    $stmt->execute([$_POST['nom'], $_POST['categorie'], $_POST['marque'], $_POST['modele'], $_POST['description'], $_POST['prix_achat'], $_POST['prix_vente'], $_POST['stock'], $_POST['garantie_mois'], $_POST['etat'], $id]);
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = "negoce_{$id}.{$ext}";
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/negoce/" . $filename);
        $pdo->prepare("UPDATE negoce SET image = ? WHERE id = ?")->execute(["uploads/negoce/$filename", $id]);
    }
    echo "<div class='alert alert-success'>✅ Produit modifié</div>";
}

// Suppression d'un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer') {
    $id = $_POST['id'];
    $pdo->prepare("DELETE FROM negoce WHERE id = ?")->execute([$id]);
    echo "<div class='alert alert-warning'>🗑️ Produit supprimé</div>";
}

$produits = $pdo->query("SELECT * FROM negoce ORDER BY date_ajout DESC")->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<h2><i class="fas fa-laptop"></i> Administration Négoce</h2>

<!-- Formulaire d'ajout -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">➕ Ajouter un produit</div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="ajouter">
            <div class="row">
                <div class="col-md-3"><input type="text" name="nom" class="form-control mb-2" placeholder="Nom" required></div>
                <div class="col-md-2">
                    <select name="categorie" class="form-select mb-2">
                        <option value="telephone">📱 Téléphone</option>
                        <option value="informatique">💻 Informatique</option>
                        <option value="electromenager">🧊 Électroménager</option>
                        <option value="mobilier">🛋️ Mobilier</option>
                        <option value="vehicule">🚗 Véhicule</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="text" name="marque" class="form-control mb-2" placeholder="Marque"></div>
                <div class="col-md-2"><input type="text" name="modele" class="form-control mb-2" placeholder="Modèle"></div>
                <div class="col-md-3">
                    <select name="etat" class="form-select mb-2">
                        <option value="neuf">Neuf</option>
                        <option value="occasion">Occasion</option>
                        <option value="reconditionne">Reconditionné</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3"><input type="number" step="0.01" name="prix_achat" class="form-control mb-2" placeholder="Prix achat €"></div>
                <div class="col-md-3"><input type="number" step="0.01" name="prix_vente" class="form-control mb-2" placeholder="Prix vente €"></div>
                <div class="col-md-2"><input type="number" name="stock" class="form-control mb-2" placeholder="Stock"></div>
                <div class="col-md-2"><input type="number" name="garantie_mois" class="form-control mb-2" placeholder="Garantie (mois)"></div>
                <div class="col-md-2"><input type="file" name="image" class="form-control mb-2" accept="image/*"></div>
            </div>
            <div class="row">
                <div class="col-md-12"><textarea name="description" class="form-control mb-2" rows="2" placeholder="Description"></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </form>
    </div>
</div>

<!-- Liste des produits avec formulaire de modification individuel -->
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
                        <img src="<?= $p['image'] ?>" style="max-width:80px; max-height:80px; border-radius:10px;">
                    <?php else: ?>
                        <div style="width:80px;height:80px;background:#eee;border-radius:10px;"></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3"><input type="text" name="nom" class="form-control mb-2" value="<?= htmlspecialchars($p['nom']) ?>" required></div>
                <div class="col-md-2">
                    <select name="categorie" class="form-select mb-2">
                        <option value="telephone" <?= $p['categorie']=='telephone'?'selected':'' ?>>📱 Téléphone</option>
                        <option value="informatique" <?= $p['categorie']=='informatique'?'selected':'' ?>>💻 Informatique</option>
                        <option value="electromenager" <?= $p['categorie']=='electromenager'?'selected':'' ?>>🧊 Électroménager</option>
                        <option value="mobilier" <?= $p['categorie']=='mobilier'?'selected':'' ?>>🛋️ Mobilier</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="text" name="marque" class="form-control mb-2" value="<?= htmlspecialchars($p['marque']) ?>"></div>
                <div class="col-md-3"><input type="text" name="modele" class="form-control mb-2" value="<?= htmlspecialchars($p['modele']) ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-2"><input type="number" step="0.01" name="prix_achat" class="form-control mb-2" value="<?= $p['prix_achat'] ?>" placeholder="Prix achat"></div>
                <div class="col-md-2"><input type="number" step="0.01" name="prix_vente" class="form-control mb-2" value="<?= $p['prix_vente'] ?>" placeholder="Prix vente"></div>
                <div class="col-md-2"><input type="number" name="stock" class="form-control mb-2" value="<?= $p['stock'] ?>" placeholder="Stock"></div>
                <div class="col-md-2"><input type="number" name="garantie_mois" class="form-control mb-2" value="<?= $p['garantie_mois'] ?>" placeholder="Garantie"></div>
                <div class="col-md-2">
                    <select name="etat" class="form-select mb-2">
                        <option value="neuf" <?= $p['etat']=='neuf'?'selected':'' ?>>Neuf</option>
                        <option value="occasion" <?= $p['etat']=='occasion'?'selected':'' ?>>Occasion</option>
                        <option value="reconditionne" <?= $p['etat']=='reconditionne'?'selected':'' ?>>Reconditionné</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="file" name="image" class="form-control mb-2" accept="image/*"></div>
            </div>
            <div class="row">
                <div class="col-md-12"><textarea name="description" class="form-control mb-2" rows="2" placeholder="Description"><?= htmlspecialchars($p['description']) ?></textarea></div>
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
