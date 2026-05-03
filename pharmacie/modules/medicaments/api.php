<?php
header('Content-Type: application/json');
require_once '../../core/Database.php';

// On récupère 'q' ou 'query' pour être sûr
$q = $_GET['q'] ?? $_GET['query'] ?? '';

try {
    if (empty($q)) {
        echo json_encode([]);
        exit;
    }

    // Recherche insensible à la casse avec TRIM
    $searchTerm = "%" . trim($q) . "%";
    
    $sql = "SELECT id, denomination, prix_vente, stock_actuel 
            FROM medicaments 
            WHERE denomination LIKE ? 
            OR code_barre LIKE ? 
            LIMIT 10";
            
    $results = Database::query($sql, [$searchTerm, $searchTerm]);
    
    echo json_encode($results);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
