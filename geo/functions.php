<?php
// Connexion à la base de données
$host = '127.0.0.1';
$db   = 'geo';
$user = 'root'; // À adapter
$pass = '123';     // À adapter

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction pour transformer l'adresse en coordonnées GPS
function getCoordinates($adresse) {
    // On ajoute un User-Agent pour respecter les règles d'OpenStreetMap
    $opts = ['http' => ['header' => "User-Agent: MonAppMedicale/1.0\r\n"]];
    $context = stream_context_create($opts);
    
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($adresse);
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);

    if (!empty($data)) {
        return [
            'lat' => $data[0]['lat'],
            'lon' => $data[0]['lon']
        ];
    }
    return null;
}
?>
