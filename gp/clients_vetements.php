<?php require_once 'auth.php'; require_once 'db_connect.php'; include('header.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<h2><i class="fas fa-user-plus"></i> Gestion clients (boutique vêtements)</h2>
<?php
// Ajout / modification client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $id = $_POST['id'] ?? null;
    $nom = $_POST['nom'];
    $tel = $_POST['telephone'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];
    if ($id) {
        $pdo->prepare("UPDATE clients_vetements SET nom=?, telephone=?, email=?, adresse=? WHERE id=?")->execute([$nom, $tel, $email, $adresse, $id]);
        echo "<div class='alert alert-success'>Client modifié.</div>";
    } else {
        $pdo->prepare("INSERT INTO clients_vetements (nom, telephone, email, adresse) VALUES (?,?,?,?)")->execute([$nom, $tel, $email, $adresse]);
        echo "<div class='alert alert-success'>Client ajouté.</div>";
    }
}
// Suppression
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM clients_vetements WHERE id=?")->execute([$id]);
    echo "<div class='alert alert-warning'>Client supprimé.</div>";
}
$clients = $pdo->query("SELECT * FROM clients_vetements ORDER BY nom")->fetchAll();
?>
<div class="row">
    <div class="col-md-5">
        <div class="card p-3">
            <h4><?= isset($_GET['edit']) ? 'Modifier' : 'Ajouter' ?> un client</h4>
            <form method="post">
                <input type="hidden" name="id" id="client_id">
                <div class="mb-2">
                    <label>Nom complet</label>
                    <input type="text" name="nom" id="nom" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Téléphone (WhatsApp)</label>
                    <input type="tel" name="telephone" id="telephone" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Email</label>
                    <input type="email" name="email" id="email" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Adresse de livraison</label>
                    <textarea name="adresse" id="adresse" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" name="action" value="save" class="btn btn-primary">Enregistrer</button>
                <a href="clients_vetements.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <h4>Liste des clients</h4>
        <table class="table table-bordered">
            <tr><th>ID</th><th>Nom</th><th>Téléphone</th><th>Email</th><th>Actions</th></tr>
            <?php foreach ($clients as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['nom']) ?></td>
                <td><?= $c['telephone'] ?></td>
                <td><?= $c['email'] ?></td>
                <td>
                    <a href="clients_vetements.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="clients_vetements.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<script>
<?php if (isset($_GET['edit'])): 
    $edit = $pdo->prepare("SELECT * FROM clients_vetements WHERE id=?");
    $edit->execute([$_GET['edit']]);
    $c = $edit->fetch(); ?>
    document.getElementById('client_id').value = <?= $c['id'] ?>;
    document.getElementById('nom').value = "<?= addslashes($c['nom']) ?>";
    document.getElementById('telephone').value = "<?= $c['telephone'] ?>";
    document.getElementById('email').value = "<?= $c['email'] ?>";
    document.getElementById('adresse').value = "<?= addslashes($c['adresse']) ?>";
<?php endif; ?>
</script>
<?php include('footer.php'); ?>
