<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="container mt-4">
    <h2><i class="fas fa-map-marked-alt"></i> Cartographie des itinéraires optimisés</h2>
    <div class="alert alert-info">
        <i class="fas fa-route"></i> Algorithme du plus court chemin (Dijkstra) pour économiser le carburant
    </div>
    <div id="map" style="height: 500px; border-radius: 15px;"></div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([14.7167, -17.4677], 12);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);
    L.marker([14.7167, -17.4677]).addTo(map).bindPopup('<strong>OMEGA Transport</strong><br>Dakar, Sénégal');
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
