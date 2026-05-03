<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: modules/auth/login.php');
    exit();
}

// Rediriger selon le rôle
switch ($_SESSION['user_role']) {
    case 'admin':
        header('Location: modules/admin/dashboard.php');
        break;
    case 'caissier':
        header('Location: modules/caisse/index.php');
        break;
    case 'medecin':
        // Récupérer le service du médecin
        require_once 'config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT service_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        // Rediriger vers la consultation avec le bon service
        header('Location: modules/medecin/consultation.php?service_id=' . ($user['service_id'] ?? ''));
        break;
    case 'sagefemme':
        header('Location: modules/sagefemme/index.php');
        break;
    case 'pharmacien':
        header('Location: modules/pharmacie/index.php');
        break;
    default:
        header('Location: modules/dashboard/index.php');
}
exit();
?>
