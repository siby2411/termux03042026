<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

$upload_dir = __DIR__ . '/uploads/sponsors/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ajouter') {
        $logo = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $filename = 'sponsor_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], "uploads/sponsors/" . $filename);
            $logo = "uploads/sponsors/$filename";
        }
        $stmt = $pdo->prepare("INSERT INTO sponsors (nom, description, logo_url, site_web, telephone, email, statut) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$_POST['nom'], $_POST['description'], $logo, $_POST['site_web'], $_POST['telephone'], $_POST['email'], $_POST['statut']]);
        echo "<div class='alert alert-success'>✅ Sponsor ajouté</div>";
    }
    
    if ($action === 'modifier') {
        $id = $_POST['id'];
        $logo = $_POST['logo_existant'] ?? '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $filename = 'sponsor_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], "uploads/sponsors/" . $filename);
            $logo = "uploads/sponsors/$filename";
        }
        $stmt = $pdo->prepare("UPDATE sponsors SET nom=?, description=?, logo_url=?, site_web=?, telephone=?, email=?, statut=? WHERE id=?");
        $stmt->execute([$_POST['nom'], $_POST['description'], $logo, $_POST['site_web'], $_POST['telephone'], $_POST['email'], $_POST['statut'], $id]);
        echo "<div class='alert alert-success'>✅ Sponsor modifié</div>";
    }
    
    if ($action === 'supprimer') {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM sponsors WHERE id = ?")->execute([$id]);
        echo "<div class='alert alert-warning'>🗑️ Sponsor supprimé</div>";
    }
}

$sponsors = $pdo->query("SELECT * FROM sponsors ORDER BY date_ajout DESC")->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<h2><i class="fas fa-star"></i> Administration Sponsors</h2>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-plus-circle"></i> Ajouter / Modifier un sponsor
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" id="sponsorForm">
            <input type="hidden" name="action" id="formAction" value="ajouter">
            <input type="hidden" name="id" id="sponsorId">
            <input type="hidden" name="logo_existant" id="logoExistant">
            <div class="row">
                <div class="col-md-4"><input type="text" name="nom" id="nom" class="form-control mb-2" placeholder="Nom du sponsor" required></div>
                <div class="col-md-4"><input type="text" name="site_web" id="site_web" class="form-control mb-2" placeholder="Site web (URL)"></div>
                <div class="col-md-2"><input type="text" name="telephone" id="telephone" class="form-control mb-2" placeholder="Téléphone"></div>
                <div class="col-md-2"><input type="email" name="email" id="email" class="form-control mb-2" placeholder="Email"></div>
            </div>
            <div class="row">
                <div class="col-md-6"><input type="text" name="description" id="description" class="form-control mb-2" placeholder="Description courte"></div>
                <div class="col-md-3">
                    <select name="statut" id="statut" class="form-select mb-2">
                        <option value="actif">✅ Actif</option>
                        <option value="inactif">⛔ Inactif</option>
                    </select>
                </div>
                <div class="col-md-3"><input type="file" name="logo" id="logo" class="form-control mb-2" accept="image/*"></div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            <button type="button" class="btn btn-secondary" onclick="resetForm()">Annuler</button>
        </form>
    </div>
</div>

<h3>Liste des sponsors</h3>
<div class="row">
    <?php foreach ($sponsors as $s): ?>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-building fa-3x text-primary"></i>
                <h6 class="mt-2"><?= htmlspecialchars($s['nom']) ?></h6>
                <small><?= htmlspecialchars($s['description']) ?></small>
                <div class="mt-2">
                    <span class="badge bg-<?= $s['statut'] == 'actif' ? 'success' : 'secondary' ?>"><?= $s['statut'] ?></span>
                </div>
                <button class="btn btn-sm btn-warning mt-2" onclick='editSponsor(<?= json_encode($s) ?>)'>✏️ Modifier</button>
                <button class="btn btn-sm btn-danger mt-2" onclick="deleteSponsor(<?= $s['id'] ?>)">🗑️ Supprimer</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function editSponsor(s) {
    document.getElementById('formAction').value = 'modifier';
    document.getElementById('sponsorId').value = s.id;
    document.getElementById('nom').value = s.nom;
    document.getElementById('description').value = s.description || '';
    document.getElementById('site_web').value = s.site_web || '';
    document.getElementById('telephone').value = s.telephone || '';
    document.getElementById('email').value = s.email || '';
    document.getElementById('statut').value = s.statut;
    document.getElementById('logoExistant').value = s.logo_url || '';
    document.querySelector('.card-header').innerHTML = '<i class="fas fa-edit"></i> Modifier le sponsor';
    window.scrollTo(0, 0);
}
function resetForm() {
    document.getElementById('sponsorForm').reset();
    document.getElementById('formAction').value = 'ajouter';
    document.getElementById('sponsorId').value = '';
    document.querySelector('.card-header').innerHTML = '<i class="fas fa-plus-circle"></i> Ajouter / Modifier un sponsor';
}
function deleteSponsor(id) {
    if(confirm('Supprimer ce sponsor définitivement ?')) {
        let form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="'+id+'">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php include('footer.php'); ?>
