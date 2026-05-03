<?php
require_once '../../config/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['cashier_id'])) {
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$db = getDB();

switch ($action) {
    case 'dashboard_stats':
        // Stock critique
        $critical = $db->query("SELECT COUNT(*) FROM ingredients WHERE current_stock <= min_stock/2")->fetchColumn();
        $low = $db->query("SELECT COUNT(*) FROM ingredients WHERE current_stock <= min_stock AND current_stock > min_stock/2")->fetchColumn();
        $pending = $db->query("SELECT COUNT(*) FROM supply_orders WHERE status = 'pending'")->fetchColumn();
        $stockValue = $db->query("SELECT SUM(current_stock * unit_price) FROM ingredients")->fetchColumn();
        
        echo json_encode([
            'critical' => (int)$critical,
            'low' => (int)$low,
            'pending_orders' => (int)$pending,
            'stock_value' => (float)$stockValue
        ]);
        break;
        
    case 'alerts':
        $alerts = $db->query("SELECT * FROM ingredients WHERE current_stock <= min_stock ORDER BY (current_stock/min_stock) ASC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($alerts as &$a) {
            $a['level'] = $a['current_stock'] <= $a['min_stock']/2 ? 'critical' : 'warning';
        }
        echo json_encode($alerts);
        break;
        
    case 'recent_movements':
        $movements = $db->query("SELECT sm.*, i.ingredient_name, i.unit 
                                 FROM stock_movements sm 
                                 JOIN ingredients i ON sm.ingredient_id = i.id 
                                 ORDER BY sm.created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($movements);
        break;
        
    case 'list_ingredients':
        $ingredients = $db->query("SELECT * FROM ingredients ORDER BY ingredient_name")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($ingredients);
        break;
        
    case 'get_ingredient':
        $stmt = $db->prepare("SELECT * FROM ingredients WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;
        
    case 'save_ingredient':
        $id = $_POST['id'] ?? 0;
        $data = [
            'ingredient_name' => $_POST['ingredient_name'],
            'ingredient_code' => $_POST['ingredient_code'],
            'unit' => $_POST['unit'],
            'current_stock' => $_POST['current_stock'],
            'min_stock' => $_POST['min_stock'],
            'unit_price' => $_POST['unit_price']
        ];
        
        if ($id > 0) {
            $sql = "UPDATE ingredients SET ingredient_name=?, ingredient_code=?, unit=?, current_stock=?, min_stock=?, unit_price=? WHERE id=?";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([$data['ingredient_name'], $data['ingredient_code'], $data['unit'], $data['current_stock'], $data['min_stock'], $data['unit_price'], $id]);
        } else {
            $sql = "INSERT INTO ingredients (ingredient_name, ingredient_code, unit, current_stock, min_stock, unit_price) VALUES (?,?,?,?,?,?)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([$data['ingredient_name'], $data['ingredient_code'], $data['unit'], $data['current_stock'], $data['min_stock'], $data['unit_price']]);
        }
        
        echo json_encode(['success' => $success, 'message' => $success ? 'Enregistré' : 'Erreur']);
        break;
        
    case 'add_stock':
        $stmt = $db->prepare("UPDATE ingredients SET current_stock = current_stock + ? WHERE id = ?");
        $success = $stmt->execute([$_GET['qty'], $_GET['id']]);
        
        if ($success) {
            $db->prepare("INSERT INTO stock_movements (ingredient_id, quantity, type, reason, created_by) VALUES (?, ?, 'in', 'Ajustement manuel', ?)")
               ->execute([$_GET['id'], $_GET['qty'], $_SESSION['cashier_id']]);
        }
        
        echo json_encode(['success' => $success]);
        break;
        
    case 'delete_ingredient':
        $stmt = $db->prepare("DELETE FROM ingredients WHERE id = ?");
        echo json_encode(['success' => $stmt->execute([$_GET['id']])]);
        break;
        
    default:
        echo json_encode(['error' => 'Action non trouvée']);
}
?>

case 'remove_stock':
    $id = $_GET['id'];
    $qty = $_GET['qty'];
    
    // Vérifier le stock disponible
    $stmt = $db->prepare("SELECT current_stock FROM ingredients WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();
    
    if ($current >= $qty) {
        $stmt = $db->prepare("UPDATE ingredients SET current_stock = current_stock - ? WHERE id = ?");
        $success = $stmt->execute([$qty, $id]);
        
        if ($success) {
            $db->prepare("INSERT INTO stock_movements (ingredient_id, quantity, type, reason, created_by) VALUES (?, ?, 'out', 'Utilisation en cuisine', ?)")
               ->execute([$id, $qty, $_SESSION['cashier_id']]);
        }
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
    }
    break;
