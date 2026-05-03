#!/bin/bash
cd /root/shared/htdocs/apachewsl2026/gp

# 1. Réparer generer_qr.php (QR code)
cat > generer_qr.php <<'QR'
<?php
if (isset($_GET['colis'])) {
    $numero = $_GET['colis'];
    $url = "https://votresite.com/suivi.php?numero=" . urlencode($numero);
    if (file_exists(__DIR__ . '/phpqrcode/qrlib.php')) {
        require_once __DIR__ . '/phpqrcode/qrlib.php';
        header('Content-Type: image/png');
        QRcode::png($url, null, QR_ECLEVEL_L, 10);
    } else {
        // fallback externe gratuit
        $img = file_get_contents("https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url));
        header('Content-Type: image/png');
        echo $img;
    }
    exit;
}
?>
QR

# 2. Table prospects (si absente)
mysql -u root -p gp_db <<SQL
CREATE TABLE IF NOT EXISTS prospects_senegalais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    civilite VARCHAR(10),
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    fonction VARCHAR(150),
    association_entreprise VARCHAR(200),
    email VARCHAR(150),
    telephone VARCHAR(20),
    adresse TEXT,
    code_postal VARCHAR(10),
    ville VARCHAR(100),
    region VARCHAR(100),
    type_contact ENUM('association','restaurant','influenceur','foire','autre') DEFAULT 'autre',
    notes TEXT,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL

# 3. admin_prospects.php complet (avec export PDF)
cat > admin_prospects.php <<'PRO'
<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
if (!class_exists('FPDF')) require_once 'fpdf186/fpdf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $civilite = $_POST['civilite']; $nom = $_POST['nom']; $prenom = $_POST['prenom'];
    $fonction = $_POST['fonction']; $asso = $_POST['association_entreprise']; $email = $_POST['email'];
    $tel = $_POST['telephone']; $adresse = $_POST['adresse']; $cp = $_POST['code_postal'];
    $ville = $_POST['ville']; $region = $_POST['region']; $type = $_POST['type_contact']; $notes = $_POST['notes'];
    if ($id) {
        $pdo->prepare("UPDATE prospects_senegalais SET civilite=?, nom=?, prenom=?, fonction=?, association_entreprise=?, email=?, telephone=?, adresse=?, code_postal=?, ville=?, region=?, type_contact=?, notes=? WHERE id=?")
           ->execute([$civilite, $nom, $prenom, $fonction, $asso, $email, $tel, $adresse, $cp, $ville, $region, $type, $notes, $id]);
        echo "<div class='alert alert-success'>Prospect modifié.</div>";
    } else {
        $pdo->prepare("INSERT INTO prospects_senegalais (civilite, nom, prenom, fonction, association_entreprise, email, telephone, adresse, code_postal, ville, region, type_contact, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
           ->execute([$civilite, $nom, $prenom, $fonction, $asso, $email, $tel, $adresse, $cp, $ville, $region, $type, $notes]);
        echo "<div class='alert alert-success'>Prospect ajouté.</div>";
    }
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM prospects_senegalais WHERE id=?")->execute([$_GET['delete']]);
    echo "<div class='alert alert-warning'>Prospect supprimé.</div>";
}
if (isset($_GET['export_pdf'])) {
    $prospects = $pdo->query("SELECT * FROM prospects_senegalais ORDER BY nom")->fetchAll();
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(190,10,"Liste des prospects - Dieynaba Keita",0,1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(30,7,"Nom",1); $pdf->Cell(40,7,"Association",1); $pdf->Cell(50,7,"Email",1); $pdf->Cell(30,7,"Tel",1); $pdf->Cell(40,7,"Ville",1); $pdf->Ln();
    $pdf->SetFont('Arial','',9);
    foreach ($prospects as $p) {
        $pdf->Cell(30,6,substr($p['nom'].' '.$p['prenom'],0,28),1);
        $pdf->Cell(40,6,substr($p['association_entreprise'],0,38),1);
        $pdf->Cell(50,6,substr($p['email'],0,48),1);
        $pdf->Cell(30,6,$p['telephone'],1);
        $pdf->Cell(40,6,substr($p['ville'],0,38),1);
        $pdf->Ln();
    }
    $pdf->Output('D','prospects.pdf');
    exit;
}
$list = $pdo->query("SELECT * FROM prospects_senegalais ORDER BY nom")->fetchAll();
?>
<h2>Gestion des prospects Sénégalais en France</h2>
<div class="row">
    <div class="col-md-5">
        <div class="card p-3 mb-4">
            <form method="post">
                <input type="hidden" name="id" id="prospect_id">
                <div class="mb-2"><select name="civilite" class="form-select"><option>M.</option><option>Mme</option><option>Dr</option></select></div>
                <div class="mb-2"><input type="text" name="nom" id="nom" class="form-control" placeholder="Nom *" required></div>
                <div class="mb-2"><input type="text" name="prenom" id="prenom" class="form-control" placeholder="Prénom"></div>
                <div class="mb-2"><input type="text" name="fonction" id="fonction" class="form-control" placeholder="Fonction"></div>
                <div class="mb-2"><input type="text" name="association_entreprise" id="asso" class="form-control" placeholder="Association"></div>
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
        <a href="admin_prospects.php?export_pdf=1" class="btn btn-danger mb-2">📄 Exporter PDF</a>
        <table class="table table-bordered">
            <tr><th>Nom</th><th>Association</th><th>Email</th><th>Tél</th><th>Ville</th><th>Actions</th></tr>
            <?php foreach ($list as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['civilite'].' '.$p['nom'].' '.$p['prenom']) ?></td>
                <td><?= htmlspecialchars($p['association_entreprise']) ?></td>
                <td><?= $p['email'] ?></td>
                <td><?= $p['telephone'] ?></td>
                <td><?= $p['ville'] ?></td>
                <td><a href="admin_prospects.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="admin_prospects.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                 </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<script>
<?php if (isset($_GET['edit'])): $edit = $pdo->prepare("SELECT * FROM prospects_senegalais WHERE id=?"); $edit->execute([$_GET['edit']]); $p = $edit->fetch(); ?>
    document.getElementById('prospect_id').value = <?= $p['id'] ?>;
    document.getElementById('civilite').value = "<?= $p['civilite'] ?>";
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
    document.getElementById('type').value = "<?= $p['type_contact'] ?>";
    document.getElementById('notes').value = "<?= addslashes($p['notes']) ?>";
<?php endif; ?>
</script>
<?php include('footer.php'); ?>
PRO

# 4. S'assurer que suivi_carte.php utilise Leaflet (déjà fait)
cat > suivi_carte.php <<'CARTE'
<?php require_once 'db_connect.php'; include('header.php');
$numero = $_GET['numero'] ?? '';
$colis = null; $lat = 48.9358; $lng = 2.3580; $pos = "Saint-Denis, France (position par défaut)";
if ($numero) {
    $stmt = $pdo->prepare("SELECT * FROM colis WHERE numero_suivi = ?");
    $stmt->execute([$numero]); $colis = $stmt->fetch();
    if ($colis && !empty($colis['position_gps'])) {
        $c = explode(',', $colis['position_gps']);
        if (count($c)==2 && is_numeric($c[0])) { $lat = (float)$c[0]; $lng = (float)$c[1]; $pos = "Lat $lat, Lng $lng"; }
    }
}
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<h2>Suivi géolocalisé</h2>
<form method="get"><div class="input-group"><input type="text" name="numero" class="form-control" placeholder="N° suivi" value="<?= htmlspecialchars($numero) ?>"><button class="btn btn-primary">Localiser</button></div></form>
<?php if ($numero): ?><div id="map" style="height:500px;margin:20px 0"></div><div class="alert alert-info">Position : <?= $pos ?></div><?php if ($colis): ?><div class="card"><div class="card-body"><h5>Infos colis</h5><p><?= htmlspecialchars($colis['numero_suivi']) ?><br>Statut : <?= $colis['statut'] ?><br>MàJ : <?= $colis['derniere_mise_a_jour'] ?></p></div></div><?php endif; endif; ?>
<script>
var lat = <?= $lat ?>, lng = <?= $lng ?>;
var map = L.map('map').setView([lat, lng], 13);
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>' }).addTo(map);
L.marker([lat, lng]).addTo(map).bindPopup('Colis <?= htmlspecialchars($numero) ?>').openPopup();
</script>
<?php include('footer.php'); ?>
CARTE

echo "✅ Tous les correctifs appliqués. Redémarrez le serveur : pkill -f 'php -S' ; cd ~/shared/htdocs/apachewsl2026/gp ; php -S 0.0.0.0:8000"
