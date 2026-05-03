<?php
require_once '../includes/config.php';
require_once '../includes/notifications/notifications.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$notif = new NotificationSystem($pdo);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'get':
        $unread = $notif->getUnread($_SESSION['user_id']);
        echo json_encode([
            'success' => true,
            'count' => $notif->getCount($_SESSION['user_id']),
            'notifications' => $unread
        ]);
        break;
        
    case 'read':
        $id = $_POST['id'] ?? 0;
        $notif->markAsRead($id, $_SESSION['user_id']);
        echo json_encode(['success' => true]);
        break;
        
    case 'read_all':
        $notif->markAllAsRead($_SESSION['user_id']);
        echo json_encode(['success' => true]);
        break;
        
    case 'delete':
        $id = $_POST['id'] ?? 0;
        $notif->delete($id, $_SESSION['user_id']);
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['error' => 'Action non reconnue']);
}
?>
