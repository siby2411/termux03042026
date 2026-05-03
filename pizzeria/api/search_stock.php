<?php
require_once '../config/config.php';
header('Content-Type: application/json');

$db = getDB();
$search = $_GET['q'] ?? '';

if (strlen($search) >= 2) {
    $stmt = $db->prepare("
        SELECT id, ingredient_name, current_stock, unit, min_stock, category, unit_price 
        FROM ingredients 
        WHERE ingredient_name LIKE ? 
        AND is_active = 1 
        ORDER BY ingredient_name 
        LIMIT 20
    ");
    $stmt->execute(["%$search%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as &$r) {
        $r['status'] = $r['current_stock'] <= $r['min_stock'] ? 'danger' : 'ok';
        $r['status_text'] = $r['current_stock'] <= $r['min_stock'] ? '⚠️ Stock bas' : '✓ OK';
    }
    
    echo json_encode(['success' => true, 'data' => $results]);
} else {
    echo json_encode(['success' => true, 'data' => []]);
}
?>
