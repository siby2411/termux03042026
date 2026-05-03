<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupération des points
$ecoles = $db->query("SELECT id_ecole, nom_ecole, latitude, longitude FROM ecoles WHERE latitude IS NOT NULL")->fetchAll();
$eleves = $db->query("SELECT id_eleve, nom_eleve, prenom_eleve, latitude_prise, longitude_prise FROM eleves WHERE latitude_prise IS NOT NULL")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cartographie - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { 
            height: 600px; 
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            background: #f0f0f0;
        }
        .map-container {
            position: relative;
            width: 100%;
            min-height: 600px;
        }
        .info-panel {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .leaflet-popup-content {
            font-size: 14px;
            min-width: 200px;
        }
    </style>
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-map-marked-alt"></i> Cartographie des itinéraires</h2>
            <hr>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="info-panel">
                <h5><i class="fas fa-info-circle"></i> Légende</h5>
                <p><i class="fas fa-school" style="color: #FF5722;"></i> <strong>Écoles</strong> - Points de destination</p>
                <p><i class="fas fa-home" style="color: #4CAF50;"></i> <strong>Domiciles élèves</strong> - Points de prise en charge</p>
                <p><i class="fas fa-route" style="color: #2196F3;"></i> <strong>Itinéraires</strong> - Trajets optimisés</p>
                <hr>
                <p><small>Cliquez sur un point pour voir les détails</small></p>
            </div>
            
            <div class="info-panel">
                <h5><i class="fas fa-route"></i> Calcul d'itinéraire</h5>
                <div class="input-group mb-2">
                    <select id="startPoint" class="form-control form-control-sm">
                        <option value="">Point de départ</option>
                        <optgroup label="Écoles">
                            <?php foreach($ecoles as $e): ?>
                            <option value="<?php echo $e['latitude']; ?>,<?php echo $e['longitude']; ?>">
                                🏫 <?php echo htmlspecialchars($e['nom_ecole']); ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Domiciles élèves">
                            <?php foreach($eleves as $el): ?>
                            <option value="<?php echo $el['latitude_prise']; ?>,<?php echo $el['longitude_prise']; ?>">
                                🏠 <?php echo htmlspecialchars($el['prenom_eleve'] . ' ' . $el['nom_eleve']); ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <div class="input-group mb-2">
                    <select id="endPoint" class="form-control form-control-sm">
                        <option value="">Point d'arrivée</option>
                        <optgroup label="Écoles">
                            <?php foreach($ecoles as $e): ?>
                            <option value="<?php echo $e['latitude']; ?>,<?php echo $e['longitude']; ?>">
                                🏫 <?php echo htmlspecialchars($e['nom_ecole']); ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Domiciles élèves">
                            <?php foreach($eleves as $el): ?>
                            <option value="<?php echo $el['latitude_prise']; ?>,<?php echo $el['longitude_prise']; ?>">
                                🏠 <?php echo htmlspecialchars($el['prenom_eleve'] . ' ' . $el['nom_eleve']); ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <button class="btn btn-primary btn-sm w-100" onclick="calculerItineraire()">
                    <i class="fas fa-calculator"></i> Calculer l'itinéraire
                </button>
                <div id="distanceInfo" class="mt-2" style="display: none;">
                    <hr>
                    <p><strong>Distance:</strong> <span id="distance">0</span> km</p>
                    <p><strong>Durée estimée:</strong> <span id="duree">0</span> min</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="map-container">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Initialisation de la carte centrée sur Dakar
var map = L.map('map').setView([14.7167, -17.4677], 13);
var routingControl = null;
var markers = [];

// Fond de carte stable
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> | OMEGA Consulting',
    maxZoom: 19,
    minZoom: 10
}).addTo(map);

// Icônes personnalisées
var schoolIcon = L.divIcon({
    className: 'custom-div-icon',
    html: '<div style="background:#FF5722; width:16px; height:16px; border-radius:50%; border:2px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.3);"></div>',
    iconSize: [16, 16],
    popupAnchor: [0, -8]
});

var homeIcon = L.divIcon({
    className: 'custom-div-icon',
    html: '<div style="background:#4CAF50; width:14px; height:14px; border-radius:50%; border:2px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.3);"></div>',
    iconSize: [14, 14],
    popupAnchor: [0, -7]
});

// Ajout des marqueurs écoles
<?php foreach($ecoles as $ecole): ?>
    var marker = L.marker([<?php echo $ecole['latitude']; ?>, <?php echo $ecole['longitude']; ?>], {icon: schoolIcon})
        .bindPopup('<strong>🏫 <?php echo addslashes($ecole['nom_ecole']); ?></strong><br>Point de destination<br><button class="btn btn-sm btn-primary mt-2" onclick="setDestination(<?php echo $ecole['latitude']; ?>, <?php echo $ecole['longitude']; ?>)">Définir comme destination</button>')
        .addTo(map);
    markers.push(marker);
<?php endforeach; ?>

// Ajout des marqueurs domiciles élèves
<?php foreach($eleves as $eleve): ?>
    var marker = L.marker([<?php echo $eleve['latitude_prise']; ?>, <?php echo $eleve['longitude_prise']; ?>], {icon: homeIcon})
        .bindPopup('<strong>🏠 <?php echo addslashes($eleve['prenom_eleve'] . ' ' . $eleve['nom_eleve']); ?></strong><br>Point de prise en charge<br><button class="btn btn-sm btn-primary mt-2" onclick="setDepart(<?php echo $eleve['latitude_prise']; ?>, <?php echo $eleve['longitude_prise']; ?>)">Définir comme départ</button>')
        .addTo(map);
    markers.push(marker);
<?php endforeach; ?>

function setDepart(lat, lng) {
    document.getElementById('startPoint').value = lat + ',' + lng;
    calculerItineraire();
}

function setDestination(lat, lng) {
    document.getElementById('endPoint').value = lat + ',' + lng;
    calculerItineraire();
}

function calculerItineraire() {
    var start = document.getElementById('startPoint').value;
    var end = document.getElementById('endPoint').value;
    
    if(!start || !end) {
        alert('Veuillez sélectionner un point de départ et un point d\'arrivée');
        return;
    }
    
    var startLatLng = start.split(',');
    var endLatLng = end.split(',');
    
    // Supprimer l'ancien itinéraire
    if(routingControl) {
        map.removeControl(routingControl);
    }
    
    // Créer le nouvel itinéraire
    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(parseFloat(startLatLng[0]), parseFloat(startLatLng[1])),
            L.latLng(parseFloat(endLatLng[0]), parseFloat(endLatLng[1]))
        ],
        routeWhileDragging: false,
        showAlternatives: false,
        fitSelectedRoutes: true,
        lineOptions: {
            styles: [{color: '#2196F3', weight: 4, opacity: 0.8}],
            extendToWaypoints: true,
            missingRouteTolerance: 0
        },
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1'
        })
    }).addTo(map);
    
    routingControl.on('routesfound', function(e) {
        var route = e.routes[0];
        var distanceKm = (route.summary.totalDistance / 1000).toFixed(2);
        var durationMin = (route.summary.totalTime / 60).toFixed(0);
        
        document.getElementById('distance').innerText = distanceKm;
        document.getElementById('duree').innerText = durationMin;
        document.getElementById('distanceInfo').style.display = 'block';
    });
}

// Ajuster la taille de la carte au chargement
setTimeout(function() {
    map.invalidateSize();
}, 500);

// Recalculer la taille lors du redimensionnement
window.addEventListener('resize', function() {
    map.invalidateSize();
});
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
