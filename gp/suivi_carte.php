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
