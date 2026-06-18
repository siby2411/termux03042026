<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$id_etudiant = intval($_GET['id'] ?? 0);

$query = "SELECT e.jour, e.heure_debut, e.heure_fin, e.salle, u.nom_uv, p.nom as prof_nom 
          FROM emploi_temps e
          JOIN affectations a ON e.affectation_id = a.id
          JOIN uvs u ON a.uv_id = u.id
          JOIN professeurs p ON a.prof_id = p.id_prof
          WHERE a.classe_id = (SELECT classe_id FROM etudiants WHERE id = $id_etudiant)";

$result = $conn->query($query);
$events = [];

while ($row = $result->fetch_assoc()) {
    // Conversion simple pour FullCalendar (nécessite une date de référence)
    $events[] = [
        'title' => $row['nom_uv'] . ' (' . $row['prof_nom'] . ')',
        'start' => date('Y-m-d') . 'T' . $row['heure_debut'],
        'end'   => date('Y-m-d') . 'T' . $row['heure_fin'],
        'location' => $row['salle']
    ];
}
header('Content-Type: application/json');
echo json_encode($events);
