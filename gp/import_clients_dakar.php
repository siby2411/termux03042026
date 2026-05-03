<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO clients_dakar (nom_complet, telephone, adresse_dakar, nom_enfant_france, tel_enfant_france, ville_enfant, copine_referent, consentement, date_contact, source) VALUES (?,?,?,?,?,?,?,?,NOW(),?)");
    $stmt->execute([
        $_POST['nom_complet'],
        $_POST['telephone'],
        $_POST['adresse_dakar'],
        $_POST['nom_enfant_france'],
        $_POST['tel_enfant_france'],
        $_POST['ville_enfant'],
        $_POST['copine_referent'],
        isset($_POST['consentement']) ? 1 : 0,
        $_POST['source']
    ]);
    echo "<div class='alert alert-success'>Client ajouté à la base Dakar.</div>";
}
?>
<h2>Fiche client Dakar (réseau voisinage / copines)</h2>
<form method="post">
    <div class="row">
        <div class="col-md-6"><input type="text" name="nom_complet" class="form-control mb-2" placeholder="Nom complet" required></div>
        <div class="col-md-6"><input type="tel" name="telephone" class="form-control mb-2" placeholder="Téléphone" required></div>
        <div class="col-12"><textarea name="adresse_dakar" class="form-control mb-2" placeholder="Adresse à Dakar"></textarea></div>
        <div class="col-md-4"><input type="text" name="nom_enfant_france" class="form-control mb-2" placeholder="Nom de l'enfant en France"></div>
        <div class="col-md-4"><input type="tel" name="tel_enfant_france" class="form-control mb-2" placeholder="Téléphone enfant en France"></div>
        <div class="col-md-4"><input type="text" name="ville_enfant" class="form-control mb-2" placeholder="Ville en France (ex: Paris)"></div>
        <div class="col-md-6"><input type="text" name="copine_referent" class="form-control mb-2" placeholder="Copine / relais"></div>
        <div class="col-md-6">
            <select name="source" class="form-select mb-2">
                <option value="flyer">Flyer</option><option value="copine">Copine</option>
                <option value="voisinage">Voisinage</option><option value="marche">Marché</option>
            </select>
        </div>
        <div class="col-12 mb-2"><label><input type="checkbox" name="consentement"> Consentement à être contacté</label></div>
        <button class="btn btn-primary">Enregistrer le contact Dakar</button>
    </div>
</form>
<?php include('footer.php'); ?>
