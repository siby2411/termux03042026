<?php
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$q = $_GET['q'] ?? '';
$type = $_GET['type'] ?? 'eleves';
$suggestions = [];

if($type == 'eleves') {
    $query = "SELECT CONCAT(nom_eleve, ' ', prenom_eleve) as label, 
                     CONCAT('Classe: ', classe, ' | École: ', (SELECT nom_ecole FROM ecoles WHERE id_ecole = eleves.id_ecole)) as info
              FROM eleves 
              WHERE nom_eleve LIKE ? OR prenom_eleve LIKE ? 
              LIMIT 10";
    $stmt = $db->prepare($query);
    $param = "%$q%";
    $stmt->execute([$param, $param]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif($type == 'parents') {
    $query = "SELECT CONCAT(nom, ' ', prenom) as label, telephone as info 
              FROM parents 
              WHERE nom LIKE ? OR prenom LIKE ? OR telephone LIKE ?
              LIMIT 10";
    $stmt = $db->prepare($query);
    $param = "%$q%";
    $stmt->execute([$param, $param, $param]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif($type == 'chauffeurs') {
    $query = "SELECT CONCAT(nom, ' ', prenom) as label, 
                     CONCAT('Permis: ', permis_conduire) as info
              FROM chauffeurs 
              WHERE nom LIKE ? OR prenom LIKE ? OR telephone LIKE ?
              LIMIT 10";
    $stmt = $db->prepare($query);
    $param = "%$q%";
    $stmt->execute([$param, $param, $param]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

header('Content-Type: application/json');
echo json_encode($suggestions);
?>
