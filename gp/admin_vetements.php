<?php require_once 'auth.php'; require_once 'db_connect.php'; include('header.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<h2><i class="fas fa-tshirt"></i> Gestion des vêtements (CRUD + upload image)</h2>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['id'] ?? null;
    $nom = $_POST['nom'];
    $desc = $_POST['description'];
    $categorie = $_POST['categorie'];
    $prix = $_POST['prix'];
    $tailles = $_POST['tailles'];
    $couleurs = $_POST['couleurs'];
    $stock = $_POST['stock'];
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'vetement_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/vetements/' . $filename);
            $image = 'uploads/vetements/' . $filename;
        } else {
            echo "<div class='alert alert-danger'>Format non autorisé (jpg, png, gif, webp).</div>";
        }
    } else {
        $image = $_POST['image_existante'] ?? '';
    }
    if ($id) {
        $pdo->prepare("UPDATE vetements SET nom=?, description=?, categorie=?, prix=?, tailles=?, couleurs=?, stock=?, image=? WHERE id=?")->execute([$nom, $desc, $categorie, $prix, $tailles, $couleurs, $stock, $image, $id]);
        echo "<div class='alert alert-success'>Vêtement modifié.</div>";
    } else {
        $pdo->prepare("INSERT INTO vetements (nom, description, categorie, prix, tailles, couleurs, stock, image) VALUES (?,?,?,?,?,?,?,?)")->execute([$nom, $desc, $categorie, $prix, $tailles, $couleurs, $stock, $image]);
        echo "<div class='alert alert-success'>Vêtement ajouté.</div>";
    }
}
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM vetements WHERE id=?")->execute([$id]);
    echo "<div class='alert alert-warning'>Vêtement supprimé.</div>";
}
$vetements = $pdo->query("SELECT * FROM vetements ORDER BY date_ajout DESC")->fetchAll();
?>
<div class="row">
    <div class="col-md-5">
        <div class="card p-3">
            <h4><?= isset($_GET['edit']) ? 'Modifier' : 'Ajouter' ?> un vêtement</h4>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" id="prod_id">
                <div class="mb-2">
                    <label>Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Description</label>
                    <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label>Catégorie</label>
                    <select name="categorie" id="categorie" class="form-select">
                        <option value="Homme">Homme</option>
                        <option value="Femme">Femme</option>
                        <option value="Enfant">Enfant</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Prix (€)</label>
                    <input type="number" step="0.01" name="prix" id="prix" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Tailles (ex: S,M,L)</label>
                    <input type="text" name="tailles" id="tailles" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Couleurs (ex: Rouge,Bleu)</label>
                    <input type="text" name="couleurs" id="couleurs" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Stock</label>
                    <input type="number" name="stock" id="stock" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <input type="hidden" name="image_existante" id="image_existante">
                </div>
                <button type="submit" name="action" value="save" class="btn btn-primary">Enregistrer</button>
                <a href="admin_vetements.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <h4>Liste des vêtements</h4>
        <table class="table table-bordered">
            <tr><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Image</th><th>Actions</th></tr>
            <?php foreach ($vetements as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['nom']) ?></td>
                <td><?= $v['categorie'] ?></td>
                <td><?= $v['prix'] ?> €</td>
                <td><?= $v['stock'] ?>
                <tr>
                    <?php if ($v['image'] && file_exists($v['image'])): ?>
                        <img src="<?= $v['image'] ?>" width="50">
                    <?php else: ?>-
                    <?php endif; ?>
                </td>
                <td>
                    <a href="admin_vetements.php?edit=<?= $v['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="admin_vetements.php?delete=<?= $v['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                 </td>
             </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<script>
<?php if (isset($_GET['edit'])): 
    $edit = $pdo->prepare("SELECT * FROM vetements WHERE id=?");
    $edit->execute([$_GET['edit']]);
    $v = $edit->fetch(); ?>
    document.getElementById('prod_id').value = <?= $v['id'] ?>;
    document.getElementById('nom').value = "<?= addslashes($v['nom']) ?>";
    document.getElementById('description').value = "<?= addslashes($v['description']) ?>";
    document.getElementById('categorie').value = "<?= $v['categorie'] ?>";
    document.getElementById('prix').value = <?= $v['prix'] ?>;
    document.getElementById('tailles').value = "<?= $v['tailles'] ?>";
    document.getElementById('couleurs').value = "<?= $v['couleurs'] ?>";
    document.getElementById('stock').value = <?= $v['stock'] ?>;
    document.getElementById('image_existante').value = "<?= $v['image'] ?>";
<?php endif; ?>
</script>
<?php include('footer.php'); ?>
