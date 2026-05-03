<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>

<h2><i class="fas fa-map-marker-alt"></i> Gestion de la géolocalisation</h2>

<?php
// Mise à jour de la position d'un colis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_colis'])) {
    $colis_id = $_POST['colis_id'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $position = "$latitude,$longitude";
    $pdo->prepare("UPDATE colis SET position_gps = ? WHERE id = ?")->execute([$position, $colis_id]);
    echo "<div class='alert alert-success'>✅ Position du colis mise à jour.</div>";
}

// Mise à jour de la position d'une entité (Paris ou Dakar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_entite'])) {
    $entite_id = $_POST['entite_id'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $adresse = $_POST['adresse'];
    $pdo->prepare("UPDATE geolocalisation_entites SET latitude=?, longitude=?, adresse_complete=?, date_mise_a_jour=NOW() WHERE entite_id=?")->execute([$latitude, $longitude, $adresse, $entite_id]);
    echo "<div class='alert alert-success'>✅ Position de l'entité mise à jour.</div>";
}

// Mise à jour groupée des colis (quand Dieynaba change de ville)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_all_colis'])) {
    $nouvelle_ville = $_POST['nouvelle_ville'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $position = "$latitude,$longitude";
    
    // Mettre à jour tous les colis en transit
    $pdo->prepare("UPDATE colis SET position_gps = ? WHERE statut IN ('enregistre', 'depart', 'transit')")->execute([$position]);
    echo "<div class='alert alert-success'>✅ Tous les colis en transit ont été mis à jour vers $nouvelle_ville.</div>";
}

$colis_list = $pdo->query("SELECT id, numero_suivi, statut, position_gps FROM colis ORDER BY id DESC LIMIT 20")->fetchAll();
$entites = $pdo->query("SELECT e.*, g.latitude, g.longitude, g.adresse_complete FROM entites e JOIN geolocalisation_entites g ON e.id = g.entite_id")->fetchAll();
?>

<div class="row">
    <!-- Mise à jour par colis -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-box"></i> Mise à jour par colis
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="update_colis" value="1">
                    <div class="mb-2">
                        <label>Choisir un colis</label>
                        <select name="colis_id" class="form-select" required>
                            <?php foreach ($colis_list as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['numero_suivi'] ?> - <?= $c['statut'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Latitude</label>
                        <input type="text" name="latitude" class="form-control" placeholder="Ex: 14.7167 (Dakar) ou 48.9358 (Paris)" required>
                    </div>
                    <div class="mb-2">
                        <label>Longitude</label>
                        <input type="text" name="longitude" class="form-control" placeholder="Ex: -17.4677 (Dakar) ou 2.3580 (Paris)" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Mettre à jour ce colis</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mise à jour par entité (Paris ou Dakar) -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-building"></i> Position des entités
            </div>
            <div class="card-body">
                <?php foreach ($entites as $e): ?>
                    <form method="post" class="mb-3 p-2 border rounded">
                        <input type="hidden" name="update_entite" value="1">
                        <input type="hidden" name="entite_id" value="<?= $e['id'] ?>">
                        <h5><?= $e['nom'] ?> (<?= $e['ville'] ?>)</h5>
                        <div class="mb-2">
                            <label>Adresse</label>
                            <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($e['adresse_complete']) ?>">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label>Latitude</label>
                                <input type="text" name="latitude" class="form-control" value="<?= $e['latitude'] ?>">
                            </div>
                            <div class="col-6">
                                <label>Longitude</label>
                                <input type="text" name="longitude" class="form-control" value="<?= $e['longitude'] ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-success mt-2">Mettre à jour <?= $e['nom'] ?></button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mise à jour groupée -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <i class="fas fa-sync-alt"></i> Mise à jour groupée (quand Dieynaba change de ville)
    </div>
    <div class="card-body">
        <form method="post" class="row g-3">
            <input type="hidden" name="update_all_colis" value="1">
            <div class="col-md-4">
                <label>Nouvelle ville</label>
                <select name="nouvelle_ville" class="form-select" required>
                    <option value="Dakar">Dakar, Sénégal</option>
                    <option value="Paris">Paris, France</option>
                    <option value="Saint-Denis">Saint-Denis, France</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Latitude par défaut</label>
                <input type="text" name="latitude" class="form-control" id="def_lat" value="14.7167">
            </div>
            <div class="col-md-3">
                <label>Longitude par défaut</label>
                <input type="text" name="longitude" class="form-control" id="def_lng" value="-17.4677">
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-warning w-100">Mettre à jour tous les colis</button>
            </div>
        </form>
        <script>
            document.querySelector('select[name="nouvelle_ville"]').addEventListener('change', function() {
                const val = this.value;
                if (val === 'Dakar') {
                    document.getElementById('def_lat').value = '14.7167';
                    document.getElementById('def_lng').value = '-17.4677';
                } else if (val === 'Paris') {
                    document.getElementById('def_lat').value = '48.8566';
                    document.getElementById('def_lng').value = '2.3522';
                } else if (val === 'Saint-Denis') {
                    document.getElementById('def_lat').value = '48.9358';
                    document.getElementById('def_lng').value = '2.3580';
                }
            });
        </script>
        <p class="text-muted mt-2"><small>⚠️ Cette action mettra à jour TOUS les colis en statut "enregistre", "depart" ou "transit" avec les nouvelles coordonnées.</small></p>
    </div>
</div>

<!-- Tableau des colis avec leurs positions actuelles -->
<div class="card">
    <div class="card-header bg-dark text-white">Derniers colis et leurs positions</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr><th>N° colis</th><th>Statut</th><th>Position GPS</th><th>Action rapide</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($colis_list as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['numero_suivi']) ?></td>
                        <td><?= $c['statut'] ?></td>
                        <td><?= $c['position_gps'] ?? 'Non définie' ?></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="colis_id" value="<?= $c['id'] ?>">
                                <button type="submit" name="latitude" value="14.7167" class="btn btn-sm btn-outline-primary">Dakar</button>
                                <button type="submit" name="latitude" value="48.8566" class="btn btn-sm btn-outline-success">Paris</button>
                                <input type="hidden" name="longitude" value="-17.4677" id="lng_<?= $c['id'] ?>">
                                <input type="hidden" name="update_colis" value="1">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
