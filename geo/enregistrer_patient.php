// 1. Récupération de l'adresse depuis le formulaire
$adresse_complete = $_POST['rue'] . ", " . $_POST['ville'];

// 2. Utilisation de la fonction pour obtenir les points GPS
$coords = getCoordinates($adresse_complete);

if ($coords) {
    $lat = $coords['lat'];
    $lon = $coords['lon'];

    // 3. Insertion en base de données
    $sql = "INSERT INTO patients (nom, latitude, longitude, diagnostic_code) 
            VALUES (:nom, :lat, :lon, :diag)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nom'  => $_POST['nom'],
        'lat'  => $lat,
        'lon'  => $lon,
        'diag' => $_POST['diagnostic']
    ]);
}
