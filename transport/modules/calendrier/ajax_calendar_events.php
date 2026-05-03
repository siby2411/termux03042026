<?php
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$start = $_GET['start'] ?? date('Y-m-d');
$end = $_GET['end'] ?? date('Y-m-d', strtotime('+30 days'));
$bus_id = $_GET['bus_id'] ?? '';
$chauffeur_id = $_GET['chauffeur_id'] ?? '';
$ecole_id = $_GET['ecole_id'] ?? '';

$query = "SELECT i.id_itineraire, i.distance_km, i.duree_estimee, 
                 b.immatriculation as bus, 
                 CONCAT(c.nom, ' ', c.prenom) as chauffeur,
                 ec.nom_ecole, ec.horaire_matin, ec.horaire_soir,
                 COUNT(a.id_eleve) as nb_eleves,
                 'matin' as type_trajet
          FROM itineraire i
          JOIN bus b ON i.id_bus = b.id_bus
          JOIN chauffeurs c ON i.id_chauffeur = c.id_chauffeur
          JOIN ecoles ec ON i.id_ecole = ec.id_ecole
          LEFT JOIN affectations a ON i.id_bus = a.id_bus AND a.sens_trajet = 'matin_ecole'
          WHERE 1=1";
          
if($bus_id) $query .= " AND i.id_bus = $bus_id";
if($chauffeur_id) $query .= " AND i.id_chauffeur = $chauffeur_id";
if($ecole_id) $query .= " AND i.id_ecole = $ecole_id";

$query .= " GROUP BY i.id_itineraire
            UNION
            SELECT i.id_itineraire, i.distance_km, i.duree_estimee, 
                   b.immatriculation, 
                   CONCAT(c.nom, ' ', c.prenom),
                   ec.nom_ecole, ec.horaire_matin, ec.horaire_soir,
                   COUNT(a.id_eleve),
                   'soir' as type_trajet
            FROM itineraire i
            JOIN bus b ON i.id_bus = b.id_bus
            JOIN chauffeurs c ON i.id_chauffeur = c.id_chauffeur
            JOIN ecoles ec ON i.id_ecole = ec.id_ecole
            LEFT JOIN affectations a ON i.id_bus = a.id_bus AND a.sens_trajet = 'soir_domicile'
            WHERE 1=1";
            
if($bus_id) $query .= " AND i.id_bus = $bus_id";
if($chauffeur_id) $query .= " AND i.id_chauffeur = $chauffeur_id";
if($ecole_id) $query .= " AND i.id_ecole = $ecole_id";
$query .= " GROUP BY i.id_itineraire";

$stmt = $db->prepare($query);
$stmt->execute();

$events = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Générer des événements pour chaque jour ouvrable (Lundi-Vendredi)
    $current = new DateTime($start);
    $end_date = new DateTime($end);
    
    while($current <= $end_date) {
        if($current->format('N') < 6) { // Lundi=1 à Vendredi=5
            $heure = $row['type_trajet'] == 'matin' ? $row['horaire_matin'] : $row['horaire_soir'];
            $date_str = $current->format('Y-m-d');
            $datetime = $date_str . ' ' . $heure;
            
            $events[] = [
                'id' => $row['id_itineraire'] . '_' . $row['type_trajet'] . '_' . $date_str,
                'title' => $row['type_trajet'] == 'matin' ? '🚌 Dépôt → ' . $row['nom_ecole'] : '🏠 Retour → Domicile',
                'start' => $datetime,
                'end' => date('Y-m-d H:i:s', strtotime($datetime . ' + ' . $row['duree_estimee'])),
                'backgroundColor' => $row['type_trajet'] == 'matin' ? '#2196F3' : '#FF9800',
                'extendedProps' => [
                    'chauffeur' => $row['chauffeur'],
                    'bus' => $row['bus'],
                    'nb_eleves' => $row['nb_eleves'],
                    'type' => $row['type_trajet'],
                    'distance' => $row['distance_km']
                ]
            ];
        }
        $current->modify('+1 day');
    }
}

header('Content-Type: application/json');
echo json_encode($events);
?>
