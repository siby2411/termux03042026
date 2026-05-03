<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .gps-card { transition: transform 0.2s; cursor: pointer; }
    .gps-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .selected-point { border: 2px solid #ff8c00; background: #fff3e0; }
    .map-container { height: 400px; border-radius: 15px; margin-bottom: 20px; }
    .btn-copy { background: #f0f0f0; border: none; padding: 2px 8px; border-radius: 5px; cursor: pointer; }
    .btn-copy:hover { background: #ff8c00; color: white; }
</style>

<h2><i class="fas fa-map-marker-alt"></i> Base de données des coordonnées GPS</h2>
<p class="text-muted">Points de géolocalisation pour le suivi des colis - Dakar & Paris / Saint-Denis</p>

<?php
// Tableau complet des coordonnées GPS
$gps_points = [
    // ==================== SÉNÉGAL - DAKAR ====================
    'Dakar - Plateau (Centre-ville)' => ['lat' => 14.6650, 'lng' => -17.4365, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'centre'],
    'Dakar - Hann Maristes' => ['lat' => 14.7225, 'lng' => -17.4308, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'quartier'],
    'Dakar - Hann Bel-Air' => ['lat' => 14.7180, 'lng' => -17.4340, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'quartier'],
    'Dakar - Yoff' => ['lat' => 14.7560, 'lng' => -17.4666, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'quartier'],
    'Dakar - Yoff Aéroport (DSS)' => ['lat' => 14.7400, 'lng' => -17.4670, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'aeroport'],
    'Dakar - Quakam' => ['lat' => 14.7350, 'lng' => -17.4450, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'quartier'],
    'Dakar - Quakam Cité Douane' => ['lat' => 14.7385, 'lng' => -17.4480, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'quartier'],
    'Dakar - HLM Grand Yoff' => ['lat' => 14.7330, 'lng' => -17.4540, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'hlm'],
    'Dakar - HLM Patte d\'Oie' => ['lat' => 14.6880, 'lng' => -17.4670, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'hlm'],
    'Dakar - Colobane' => ['lat' => 14.6840, 'lng' => -17.4460, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'marche'],
    'Dakar - Grand Dakar' => ['lat' => 14.6920, 'lng' => -17.4480, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'quartier'],
    'Dakar - Mermoz' => ['lat' => 14.7020, 'lng' => -17.4690, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'residentiel'],
    'Dakar - Fann' => ['lat' => 14.6720, 'lng' => -17.4720, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'universite'],
    'Dakar - Point E' => ['lat' => 14.6780, 'lng' => -17.4580, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'diplomatique'],
    'Dakar - Ngor' => ['lat' => 14.7580, 'lng' => -17.5140, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'plage'],
    'Dakar - Almadies' => ['lat' => 14.7500, 'lng' => -17.4970, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'residentiel'],
    'Dakar - Ouakam' => ['lat' => 14.7280, 'lng' => -17.4870, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'quartier'],
    'Dakar - Les Mamelles' => ['lat' => 14.7320, 'lng' => -17.5000, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'touristique'],
    'Dakar - SICAP Liberté' => ['lat' => 14.7100, 'lng' => -17.4620, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'residentiel'],
    'Dakar - Dieuppeul' => ['lat' => 14.7100, 'lng' => -17.4800, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'quartier'],
    'Dakar - Fass' => ['lat' => 14.6950, 'lng' => -17.4550, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'quartier'],
    'Dakar - Parcelles Assainies' => ['lat' => 14.7450, 'lng' => -17.4750, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'hlm'],
    'Dakar - Pikine' => ['lat' => 14.7400, 'lng' => -17.3900, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'banlieue'],
    'Dakar - Rufisque' => ['lat' => 14.7200, 'lng' => -17.2800, 'ville' => 'Dakar', 'pays' => 'Sénégal', 'type' => 'banlieue'],
    
    // ==================== FRANCE - ÎLE-DE-FRANCE ====================
    'Paris - Saint-Denis (Centre)' => ['lat' => 48.9358, 'lng' => 2.3580, 'ville' => 'Saint-Denis', 'pays' => 'France', 'type' => 'centre'],
    'Paris - Basilique Saint-Denis' => ['lat' => 48.9354, 'lng' => 2.3595, 'ville' => 'Saint-Denis', 'pays' => 'France', 'type' => 'monument'],
    'Paris - Université Paris 8' => ['lat' => 48.9440, 'lng' => 2.3620, 'ville' => 'Saint-Denis', 'pays' => 'France', 'type' => 'universite'],
    'Paris - Saint-Denis Pleyel' => ['lat' => 48.9202, 'lng' => 2.3448, 'ville' => 'Saint-Denis', 'pays' => 'France', 'type' => 'affaires'],
    'Paris - Saint-Denis Porte de Paris' => ['lat' => 48.9230, 'lng' => 2.3550, 'ville' => 'Saint-Denis', 'pays' => 'France', 'type' => 'entree'],
    'Paris - La Plaine Saint-Denis' => ['lat' => 48.9163, 'lng' => 2.3540, 'ville' => 'Saint-Denis', 'pays' => 'France', 'type' => 'activites'],
    'Paris - Stade de France' => ['lat' => 48.9245, 'lng' => 2.3600, 'ville' => 'Saint-Denis', 'pays' => 'France', 'type' => 'stade'],
    'Paris - Saint-Denis Francs-Moisins' => ['lat' => 48.9360, 'lng' => 2.3490, 'ville' => 'Saint-Denis', 'pays' => 'France', 'type' => 'residentiel'],
    'Paris - Saint-Ouen' => ['lat' => 48.9050, 'lng' => 2.3300, 'ville' => 'Saint-Ouen', 'pays' => 'France', 'type' => 'marche'],
    'Paris - Aubervilliers' => ['lat' => 48.9160, 'lng' => 2.3880, 'ville' => 'Aubervilliers', 'pays' => 'France', 'type' => 'ville'],
    'Paris - Pantin' => ['lat' => 48.9000, 'lng' => 2.4100, 'ville' => 'Pantin', 'pays' => 'France', 'type' => 'ville'],
    'Paris - La Courneuve' => ['lat' => 48.9300, 'lng' => 2.3920, 'ville' => 'La Courneuve', 'pays' => 'France', 'type' => 'ville'],
    'Paris - Pierrefitte-sur-Seine' => ['lat' => 48.9660, 'lng' => 2.3450, 'ville' => 'Pierrefitte', 'pays' => 'France', 'type' => 'ville'],
    'Paris - Stains' => ['lat' => 48.9580, 'lng' => 2.3820, 'ville' => 'Stains', 'pays' => 'France', 'type' => 'ville'],
    'Paris - Épinay-sur-Seine' => ['lat' => 48.9590, 'lng' => 2.3120, 'ville' => 'Épinay', 'pays' => 'France', 'type' => 'ville'],
    'Paris - Gare du Nord' => ['lat' => 48.8809, 'lng' => 2.3550, 'ville' => 'Paris 10e', 'pays' => 'France', 'type' => 'gare'],
    'Paris - Châtelet-les-Halles' => ['lat' => 48.8613, 'lng' => 2.3473, 'ville' => 'Paris 1er', 'pays' => 'France', 'type' => 'gare'],
    'Paris - Tour Eiffel' => ['lat' => 48.8584, 'lng' => 2.2945, 'ville' => 'Paris 7e', 'pays' => 'France', 'type' => 'monument'],
    'Paris - Arc de Triomphe' => ['lat' => 48.8738, 'lng' => 2.2950, 'ville' => 'Paris 8e', 'pays' => 'France', 'type' => 'monument'],
];

// Vérifier si un colis est passé en POST pour mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['colis_id']) && isset($_POST['latitude']) && isset($_POST['longitude'])) {
    $colis_id = (int)$_POST['colis_id'];
    $lat = (float)$_POST['latitude'];
    $lng = (float)$_POST['longitude'];
    $position = "$lat,$lng";
    $pdo->prepare("UPDATE colis SET position_gps = ?, derniere_mise_a_jour = NOW() WHERE id = ?")->execute([$position, $colis_id]);
    echo "<div class='alert alert-success'>📍 Position du colis mis à jour avec succès</div>";
}

$cols = $pdo->query("SELECT id, numero_suivi, statut, position_gps FROM colis ORDER BY id DESC LIMIT 10")->fetchAll();
$point_selected = $_GET['point'] ?? '';
?>

<!-- Carte interactive -->
<div id="map" class="map-container"></div>

<!-- Filtres par région -->
<div class="row mb-4">
    <div class="col-12">
        <div class="btn-group w-100">
            <button class="btn btn-outline-primary filter-btn" data-filter="tous">🌍 Tous les points</button>
            <button class="btn btn-outline-success filter-btn" data-filter="Sénégal">🇸🇳 Sénégal - Dakar</button>
            <button class="btn btn-outline-danger filter-btn" data-filter="France">🇫🇷 France - Île-de-France</button>
        </div>
    </div>
</div>

<!-- Liste des points par pays -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-map-marker-alt"></i> 🇸🇳 Sénégal - Dakar
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <div class="row">
                    <?php foreach ($gps_points as $nom => $coord): ?>
                        <?php if ($coord['pays'] == 'Sénégal'): ?>
                            <div class="col-md-6 gps-item" data-pays="Sénégal">
                                <div class="card gps-card mb-2 <?= $point_selected == urlencode($nom) ? 'selected-point' : '' ?>" 
                                     onclick="selectPoint(<?= $coord['lat'] ?>, <?= $coord['lng'] ?>, '<?= addslashes($nom) ?>')">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><i class="fas fa-location-dot text-success"></i> <?= htmlspecialchars($nom) ?></strong><br>
                                                <small class="text-muted">📌 <?= $coord['lat'] ?>, <?= $coord['lng'] ?></small>
                                            </div>
                                            <div>
                                                <span class="badge bg-secondary"><?= $coord['type'] ?></span>
                                                <button class="btn btn-sm btn-copy" onclick="copyToClipboard('<?= $coord['lat'] ?>, <?= $coord['lng'] ?>'); event.stopPropagation();">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-map-marker-alt"></i> 🇫🇷 France - Île-de-France (Saint-Denis & Paris)
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <div class="row">
                    <?php foreach ($gps_points as $nom => $coord): ?>
                        <?php if ($coord['pays'] == 'France'): ?>
                            <div class="col-md-6 gps-item" data-pays="France">
                                <div class="card gps-card mb-2 <?= $point_selected == urlencode($nom) ? 'selected-point' : '' ?>" 
                                     onclick="selectPoint(<?= $coord['lat'] ?>, <?= $coord['lng'] ?>, '<?= addslashes($nom) ?>')">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><i class="fas fa-location-dot text-danger"></i> <?= htmlspecialchars($nom) ?></strong><br>
                                                <small class="text-muted">📌 <?= $coord['lat'] ?>, <?= $coord['lng'] ?></small>
                                            </div>
                                            <div>
                                                <span class="badge bg-secondary"><?= $coord['type'] ?></span>
                                                <button class="btn btn-sm btn-copy" onclick="copyToClipboard('<?= $coord['lat'] ?>, <?= $coord['lng'] ?>'); event.stopPropagation();">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire de mise à jour rapide -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-edit"></i> Mettre à jour la position d'un colis
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label>Sélectionner un colis</label>
                        <select id="colis_select" class="form-select">
                            <option value="">-- Choisir un colis --</option>
                            <?php foreach ($cols as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['numero_suivi']) ?> (<?= $c['statut'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Position actuelle</label>
                        <input type="text" id="current_position" class="form-control" readonly placeholder="Cliquez sur un point GPS">
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <button id="update_position_btn" class="btn btn-primary w-100" onclick="updateColisPosition()">
                            📍 Mettre à jour la position
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialisation de la carte
var map = L.map('map').setView([48.9245, 2.3600], 10);
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19
}).addTo(map);

// Ajouter tous les marqueurs
var markers = {};
var gpsData = <?php echo json_encode($gps_points); ?>;

function addMarkers(filter = 'tous') {
    // Supprimer tous les marqueurs existants
    for (var key in markers) {
        map.removeLayer(markers[key]);
    }
    markers = {};
    
    for (var nom in gpsData) {
        var coord = gpsData[nom];
        if (filter !== 'tous' && coord.pays !== filter) continue;
        
        var color = coord.pays === 'Sénégal' ? 'green' : 'red';
        var icon = L.divIcon({
            html: '<i class="fas fa-map-marker-alt" style="color:' + color + '; font-size:24px;"></i>',
            iconSize: [24, 24],
            className: 'custom-div-icon'
        });
        
        var marker = L.marker([coord.lat, coord.lng], { icon: icon }).addTo(map);
        marker.bindPopup(`
            <strong>${nom}</strong><br>
            📌 ${coord.lat}, ${coord.lng}<br>
            📍 ${coord.ville}, ${coord.pays}<br>
            <button class="btn btn-sm btn-primary mt-1" onclick="selectPoint(${coord.lat}, ${coord.lng}, '${nom}')">
                📍 Utiliser cette position
            </button>
        `);
        markers[nom] = marker;
    }
}

function selectPoint(lat, lng, nom) {
    map.setView([lat, lng], 15);
    document.getElementById('current_position').value = `${lat}, ${lng} (${nom})`;
    window.selectedLat = lat;
    window.selectedLng = lng;
    window.selectedNom = nom;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    alert('Coordonnées copiées : ' + text);
}

function updateColisPosition() {
    var colisId = document.getElementById('colis_select').value;
    if (!colisId) {
        alert('Veuillez sélectionner un colis');
        return;
    }
    if (!window.selectedLat || !window.selectedLng) {
        alert('Veuillez sélectionner un point GPS sur la carte');
        return;
    }
    
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    form.innerHTML = `
        <input type="hidden" name="colis_id" value="${colisId}">
        <input type="hidden" name="latitude" value="${window.selectedLat}">
        <input type="hidden" name="longitude" value="${window.selectedLng}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Filtres
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        var filter = this.dataset.filter;
        addMarkers(filter);
        
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Filtrer la liste
        document.querySelectorAll('.gps-item').forEach(item => {
            if (filter === 'tous' || item.dataset.pays === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Initialisation
addMarkers('tous');
</script>

<?php include('footer.php'); ?>
