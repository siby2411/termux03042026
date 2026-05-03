<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT COUNT(*) as count FROM file_attente WHERE statut = 'en_attente'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();

    echo json_encode(['count' => $result['count']]);

} catch(Exception $e) {
    echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
}
?>
