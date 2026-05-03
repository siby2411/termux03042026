<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

// Rediriger vers le dashboard spécifique selon le rôle
$role = $_SESSION['user_role'];
switch ($role) {
    case 'admin':
        header('Location: /modules/admin/dashboard.php');
        break;
    case 'doctor':
        header('Location: /modules/doctor/dashboard.php');
        break;
    case 'nurse':
        header('Location: /modules/nurse/dashboard.php');
        break;
    case 'cashier':
        header('Location: /modules/cashier/dashboard.php');
        break;
    default:
        header('Location: /modules/dashboard/general.php');
}
exit();
?>
