<?php
require_once '../config/config.php';
header('Content-Type: application/json');

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $product_code = generateCode('PIZ');
        $image_url = '';
        
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOAD_PATH . '/products/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $filename = $product_code . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $filename)) {
                $image_url = '/uploads/products/' . $filename;
            }
        }
        
        $stmt = $db->prepare("INSERT INTO products (product_code, product_name, description, category_id, unit_price, price_small, price_medium, price_large, ingredients, preparation_time, is_vegetarian, is_available, is_featured, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$product_code, $_POST['product_name'], $_POST['description'] ?? '', $_POST['category_id'], $_POST['unit_price'], $_POST['price_small'] ?? null, $_POST['price_medium'] ?? null, $_POST['price_large'] ?? null, $_POST['ingredients'] ?? '', $_POST['preparation_time'] ?? 15, $_POST['is_vegetarian'] ?? 0, 1, $_POST['is_featured'] ?? 0, $image_url]);
        echo json_encode(['success' => $success]);
    } elseif ($action == 'edit') {
        $stmt = $db->prepare("UPDATE products SET product_name=?, description=?, category_id=?, unit_price=?, price_small=?, price_medium=?, price_large=?, ingredients=?, preparation_time=?, is_vegetarian=?, is_featured=? WHERE id=?");
        $success = $stmt->execute([$_POST['product_name'], $_POST['description'] ?? '', $_POST['category_id'], $_POST['unit_price'], $_POST['price_small'] ?? null, $_POST['price_medium'] ?? null, $_POST['price_large'] ?? null, $_POST['ingredients'] ?? '', $_POST['preparation_time'] ?? 15, $_POST['is_vegetarian'] ?? 0, $_POST['is_featured'] ?? 0, $_POST['id']]);
        echo json_encode(['success' => $success]);
    } elseif ($action == 'delete') {
        $stmt = $db->prepare("DELETE FROM products WHERE id=?");
        $success = $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => $success]);
    }
}
?>
