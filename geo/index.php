<?php 
require_once 'functions.php'; 

// LOGIQUE D'ENREGISTREMENT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $ville = $_POST['ville'];
    $pathologie = $_POST['pathologie'];
    
    $coords = getCoordinates($ville);
    
    if ($coords) {
        $stmt = $pdo->prepare("INSERT INTO patients (nom, pathologie, adresse_ville, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $pathologie, $ville, $coords['lat'], $coords['lon']]);
        echo "<script>alert('Patient localisé et enregistré !');</script>";
    } else {
        echo "<script>alert('Impossible de localiser cette adresse.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cartographie Épidémiologique</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map { height: 500px; width: 100%; margin-top: 20px; border: 2px solid #ccc; }
        .form-container { padding: 20px; background: #f4f4f4; border-radius: 8px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Admission & Localisation Patient</h2>
    <form method="POST">
        <input type="text" name="nom" placeholder="Nom du Patient" required>
        <input type="text" name="ville" placeholder="Ville ou Quartier (ex: Médina, Dakar)" required>
        <input type="text" name="pathologie" placeholder="Diagnostic (ex: Grippe)" required>
        <button type="submit">Enregistrer et Cartographier</button>
    </form>
</div>

<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Initialisation de la carte sur Dakar par défaut
    var map = L.map('map').setView([14.7167, -17.4677], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    <?php
    // Extraction des patients pour la carte
    $query = $pdo->query("SELECT * FROM patients WHERE latitude IS NOT NULL");
    while ($p = $query->fetch()) {
        // On crée un marqueur pour chaque patient
        echo "L.marker([{$p['latitude']}, {$p['longitude']}])
               .addTo(map)
               .bindPopup('<b>Patient:</b> {$p['nom']}<br><b>Pathologie:</b> {$p['pathologie']}');\n";
    }
    ?>
</script>

</body>
</html>
