<?php
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$query = "INSERT INTO historique_trajets (id_bus, id_chauffeur, date_trajet, km_parcourus, carburant_consomme)
          VALUES (1, 1, CURDATE(), ?, ?)";
$stmt = $db->prepare($query);
$stmt->execute([$_POST['distance'], $_POST['carburant_economise']]);

echo json_encode(['success' => true]);
?>
