<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

header('Content-Type: application/json');

if (isset($_GET['q'])) {
    $q = "%" . $_GET['q'] . "%";
    // Augmenté à LIMIT 10 pour voir tous les filtres
    $stmt = $db->prepare("SELECT id_piece as id, nom_piece as libelle, prix_vente as prix FROM pieces_detachees WHERE nom_piece LIKE ? LIMIT 10");
    $stmt->execute([$q]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results ? $results : []);
}
