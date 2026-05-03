<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

if($_POST['action'] == 'add') {
    $query = "INSERT INTO chauffeurs (nom, prenom, telephone, permis_conduire, date_embauche, statut_chauffeur) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        $_POST['nom'], $_POST['prenom'], $_POST['telephone'],
        $_POST['permis_conduire'], $_POST['date_embauche'], $_POST['statut_chauffeur']
    ]);
    echo json_encode(['success' => $result]);
} elseif($_POST['action'] == 'toggle') {
    $stmt = $db->prepare("UPDATE chauffeurs SET statut_chauffeur = ? WHERE id_chauffeur = ?");
    echo json_encode(['success' => $stmt->execute([$_POST['status'], $_POST['id']])]);
} elseif($_POST['action'] == 'delete') {
    $stmt = $db->prepare("DELETE FROM chauffeurs WHERE id_chauffeur = ?");
    echo json_encode(['success' => $stmt->execute([$_POST['id']])]);
}
?>
