<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

if($_POST['action'] == 'add') {
    $query = "INSERT INTO eleves (nom_eleve, prenom_eleve, classe, id_ecole, id_parent, point_prise_en_charge, statut_inscription) 
              VALUES (?, ?, ?, ?, ?, ?, 'en_attente')";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        $_POST['nom_eleve'], $_POST['prenom_eleve'], $_POST['classe'],
        $_POST['id_ecole'], $_POST['id_parent'], $_POST['point_prise_en_charge'] ?? ''
    ]);
    echo json_encode(['success' => $result]);
} elseif($_POST['action'] == 'delete') {
    $stmt = $db->prepare("DELETE FROM eleves WHERE id_eleve = ?");
    echo json_encode(['success' => $stmt->execute([$_POST['id']])]);
}
?>
