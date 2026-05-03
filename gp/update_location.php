<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Mise à jour de la position d'un colis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['colis_id']) && isset($_POST['latitude']) && isset($_POST['longitude'])) {
        $colis_id = (int)$_POST['colis_id'];
        $lat = (float)$_POST['latitude'];
        $lng = (float)$_POST['longitude'];
        $position = "$lat,$lng";
        $pdo->prepare("UPDATE colis SET position_gps = ?, derniere_mise_a_jour = NOW() WHERE id = ?")->execute([$position, $colis_id]);
        echo "<div class='alert alert-success'>📍 Position du colis mise à jour</div>";
    }
    
    if (isset($_POST['update_all']) && isset($_POST['selected_point'])) {
        $point_id = (int)$_POST['selected_point'];
        $point = $pdo->prepare("SELECT * FROM geolocalisation_points WHERE id = ?");
        $point->execute([$point_id]);
        $p = $point->fetch();
        if ($p) {
            $position = "{$p['latitude']},{$p['longitude']}";
            $pdo->prepare("UPDATE colis SET position_gps = ? WHERE statut IN ('enregistre', 'depart', 'transit')")->execute([$position]);
            echo "<div class='alert alert-info'>🚚 Tous les colis en transit mis à jour vers : {$p['nom']} ({$p['ville']})</div>";
        }
    }
}

$cols = $pdo->query("SELECT id, numero_suivi, statut, position_gps FROM colis ORDER BY id DESC LIMIT 20")->fetchAll();
$points = $pdo->query("SELECT * FROM geolocalisation_points ORDER BY pays, ville, nom")->fetchAll();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<h2><i class="fas fa-map-marker-alt"></i> Mise à jour rapide des positions GPS</h2>

<div class="row">
    <!-- Mise à jour individuelle -->
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">📍 Par colis</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-2">
                        <label>Sélectionner un colis</label>
                        <select name="colis_id" class="form-select">
                            <?php foreach ($cols as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['numero_suivi']) ?> (<?= $c['statut'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Latitude</label>
                            <input type="text" name="latitude" class="form-control" placeholder="Ex: 14.7225">
                        </div>
                        <div class="col-6">
                            <label>Longitude</label>
                            <input type="text" name="longitude" class="form-control" placeholder="Ex: -17.4308">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2 w-100">Mettre à jour ce colis</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mise à jour groupée -->
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">🚀 Mise à jour groupée</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="update_all" value="1">
                    <div class="mb-2">
                        <label>Sélectionner un point GPS prédéfini</label>
                        <select name="selected_point" class="form-select">
                            <optgroup label="🇸🇳 Dakar, Sénégal">
                                <?php foreach ($points as $p): ?>
                                    <?php if ($p['pays'] == 'Sénégal'): ?>
                                        <option value="<?= $p['id'] ?>">📍 <?= $p['nom'] ?> - <?= $p['ville'] ?> (<?= $p['latitude'] ?>, <?= $p['longitude'] ?>)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="🇫🇷 Île-de-France, France">
                                <?php foreach ($points as $p): ?>
                                    <?php if ($p['pays'] == 'France'): ?>
                                        <option value="<?= $p['id'] ?>">📍 <?= $p['nom'] ?> - <?= $p['ville'] ?> (<?= $p['latitude'] ?>, <?= $p['longitude'] ?>)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Mettre à jour tous les colis en transit</button>
                </form>
                <div class="alert alert-warning mt-2 small">
                    <i class="fas fa-info-circle"></i> Cette action modifie la position de tous les colis ayant un statut "enregistre", "depart" ou "transit".
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des points GPS disponibles -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <i class="fas fa-database"></i> Base de données des points GPS
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>🇸🇳 Sénégal - Dakar</h5>
                <table class="table table-sm">
                    <?php foreach ($points as $p): ?>
                        <?php if ($p['pays'] == 'Sénégal'): ?>
                            <tr><td><?= $p['nom'] ?></td><td><?= $p['latitude'] ?></td><td><?= $p['longitude'] ?></td></tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="col-md-6">
                <h5>🇫🇷 France - Île-de-France</h5>
                <table class="table table-sm">
                    <?php foreach ($points as $p): ?>
                        <?php if ($p['pays'] == 'France'): ?>
                            <tr><td><?= $p['nom'] ?></td><td><?= $p['latitude'] ?></td><td><?= $p['longitude'] ?></td></tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
