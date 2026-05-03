<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $civilite = $_POST['civilite'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $fonction = $_POST['fonction'];
    $asso = $_POST['association_entreprise'];
    $email = $_POST['email'];
    $tel = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $cp = $_POST['code_postal'];
    $ville = $_POST['ville'];
    $region = $_POST['region'];
    $type = $_POST['type_contact'];
    $notes = $_POST['notes'];

    if ($id) {
        $sql = "UPDATE prospects_senegalais SET civilite=?, nom=?, prenom=?, fonction=?, association_entreprise=?, email=?, telephone=?, adresse=?, code_postal=?, ville=?, region=?, type_contact=?, notes=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$civilite, $nom, $prenom, $fonction, $asso, $email, $tel, $adresse, $cp, $ville, $region, $type, $notes, $id]);
        echo "<div class='alert alert-success'>Prospect modifié.</div>";
    } else {
        $sql = "INSERT INTO prospects_senegalais (civilite, nom, prenom, fonction, association_entreprise, email, telephone, adresse, code_postal, ville, region, type_contact, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$civilite, $nom, $prenom, $fonction, $asso, $email, $tel, $adresse, $cp, $ville, $region, $type, $notes]);
        echo "<div class='alert alert-success'>Prospect ajouté.</div>";
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM prospects_senegalais WHERE id=?")->execute([$id]);
    echo "<div class='alert alert-warning'>Prospect supprimé.</div>";
}

$list = $pdo->query("SELECT * FROM prospects_senegalais ORDER BY nom")->fetchAll();
?>
<h2><i class="fas fa-address-card"></i> Gestion des prospects sénégalais en France</h2>
<div class="row">
    <div class="col-md-5">
        <div class="card p-3 mb-4">
            <h4>Ajouter / Modifier un prospect</h4>
            <form method="post">
                <input type="hidden" name="id" id="prospect_id">
                <div class="mb-2"><select name="civilite" class="form-select"><option>M.</option><option>Mme</option><option>Dr</option></select></div>
                <div class="mb-2"><input type="text" name="nom" id="nom" class="form-control" placeholder="Nom *" required></div>
                <div class="mb-2"><input type="text" name="prenom" id="prenom" class="form-control" placeholder="Prénom"></div>
                <div class="mb-2"><input type="text" name="fonction" id="fonction" class="form-control" placeholder="Fonction"></div>
                <div class="mb-2"><input type="text" name="association_entreprise" id="asso" class="form-control" placeholder="Association/Entreprise"></div>
                <div class="mb-2"><input type="email" name="email" id="email" class="form-control" placeholder="Email"></div>
                <div class="mb-2"><input type="tel" name="telephone" id="tel" class="form-control" placeholder="Téléphone"></div>
                <div class="mb-2"><input type="text" name="adresse" id="adresse" class="form-control" placeholder="Adresse"></div>
                <div class="mb-2"><input type="text" name="code_postal" id="cp" class="form-control" placeholder="Code postal"></div>
                <div class="mb-2"><input type="text" name="ville" id="ville" class="form-control" placeholder="Ville"></div>
                <div class="mb-2"><input type="text" name="region" id="region" class="form-control" placeholder="Région"></div>
                <div class="mb-2"><select name="type_contact" class="form-select"><option>association</option><option>restaurant</option><option>influenceur</option><option>foire</option><option>autre</option></select></div>
                <div class="mb-2"><textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Notes"></textarea></div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="admin_prospects.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <h4>Liste des prospects</h4>
        <?php if (empty($list)): ?>
            <p>Aucun prospect. Ajoutez-en un !</p>
        <?php else: ?>
            <table class="table table-bordered">
                <tr><th>Nom</th><th>Association</th><th>Email</th><th>Téléphone</th><th>Ville</th><th>Actions</th></tr>
                <?php foreach ($list as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['civilite'].' '.$p['nom'].' '.$p['prenom']) ?></td>
                    <td><?= htmlspecialchars($p['association_entreprise']) ?> %s
                    <td><?= htmlspecialchars($p['email']) ?> %s
                    <td><?= htmlspecialchars($p['telephone']) ?> %s
                    <td><?= htmlspecialchars($p['ville']) ?> %s
                    <td>
                        <a href="admin_prospects.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="admin_prospects.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
<script>
<?php if (isset($_GET['edit'])):
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM prospects_senegalais WHERE id=?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p):
?>
    document.getElementById('prospect_id').value = <?= $p['id'] ?>;
    document.querySelector('select[name="civilite"]').value = "<?= $p['civilite'] ?>";
    document.getElementById('nom').value = "<?= addslashes($p['nom']) ?>";
    document.getElementById('prenom').value = "<?= addslashes($p['prenom']) ?>";
    document.getElementById('fonction').value = "<?= addslashes($p['fonction']) ?>";
    document.getElementById('asso').value = "<?= addslashes($p['association_entreprise']) ?>";
    document.getElementById('email').value = "<?= $p['email'] ?>";
    document.getElementById('tel').value = "<?= $p['telephone'] ?>";
    document.getElementById('adresse').value = "<?= addslashes($p['adresse']) ?>";
    document.getElementById('cp').value = "<?= $p['code_postal'] ?>";
    document.getElementById('ville').value = "<?= addslashes($p['ville']) ?>";
    document.getElementById('region').value = "<?= addslashes($p['region']) ?>";
    document.querySelector('select[name="type_contact"]').value = "<?= $p['type_contact'] ?>";
    document.getElementById('notes').value = "<?= addslashes($p['notes']) ?>";
<?php endif; endif; ?>
</script>
<?php include('footer.php'); ?>
