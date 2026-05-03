<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

if($_POST['action'] == 'add') {
    $query = "INSERT INTO bus (immatriculation, modele, capacite_max, consommation_moyenne, statut_bus) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        $_POST['immatriculation'], $_POST['modele'], $_POST['capacite_max'],
        $_POST['consommation_moyenne'] ?: null, $_POST['statut_bus']
    ]);
    echo json_encode(['success' => $result]);
} elseif($_POST['action'] == 'delete') {
    $stmt = $db->prepare("DELETE FROM bus WHERE id_bus = ?");
    echo json_encode(['success' => $stmt->execute([$_POST['id']])]);
}
?>
