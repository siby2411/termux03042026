

<?php
/**
 * Fichier : get_classes_ohada.php
 * Rôle : Récupère la liste des 9 classes OHADA pour l'interface utilisateur.
 */
require 'config.php';

header('Content-Type: application/json');

try {
    $pdo = connectDB($host, $dbName, $username, $password);
    
    $sql = "SELECT id_classe, numero_classe, nom_classe FROM classes_ohada ORDER BY numero_classe ASC";
    $stmt = $pdo->query($sql);
    
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($classes);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
