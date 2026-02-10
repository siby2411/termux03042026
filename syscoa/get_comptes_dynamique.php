

<?php
/**
 * Fichier : get_comptes_dynamique.php
 * Rôle : Fournit dynamiquement les comptes OHADA pour l'autocomplétion,
 * avec la possibilité de filtrer par Classe.
 */
require 'config.php';

header('Content-Type: application/json');

// Terme de recherche (numéro de compte ou nom)
$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';
// ID de la classe sélectionnée pour le filtrage
$id_classe = isset($_GET['id_classe']) ? (int)$_GET['id_classe'] : 0;

$results = [];

try {
    $pdo = connectDB($host, $dbName, $username, $password);

    // Construction de la requête SQL
    $sql = "SELECT numero_compte, nom_compte 
            FROM comptes_ohada 
            WHERE 1=1 "; 
    
    $params = [];

    // 1. Filtrage par Classe (si un ID est fourni et valide)
    if ($id_classe > 0) {
        $sql .= " AND id_classe_fk = :id_classe ";
        $params[':id_classe'] = $id_classe;
    }

    // 2. Filtrage par terme de recherche (numéro ou nom)
    if (!empty($searchTerm)) {
        // Ajout d'une condition OR pour la recherche multi-critères
        $sql .= " AND (numero_compte LIKE :term_num OR nom_compte LIKE :term_name) ";
        $params[':term_num'] = $searchTerm . '%';
        $params[':term_name'] = '%' . $searchTerm . '%';
    }

    $sql .= " ORDER BY numero_compte ASC LIMIT 15"; // Limiter les résultats

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Formatage des résultats
        $results[] = [
            'id' => $row['numero_compte'],
            'text' => $row['numero_compte'] . ' - ' . $row['nom_compte']
        ];
    }

    echo json_encode($results);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de recherche: ' . $e->getMessage()]);
}
?>





