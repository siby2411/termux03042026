<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function loginUser($id, $username, $role = 'admin') {
    $_SESSION['admin_id'] = $id;
    $_SESSION['admin_nom'] = $username;
    $_SESSION['admin_role'] = $role;
}

function logoutUser() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function currentUserId() {
    return $_SESSION['admin_id'] ?? null;
}

function currentUserName() {
    return $_SESSION['admin_nom'] ?? '';
}

function currentUserRole() {
    return $_SESSION['admin_role'] ?? 'user';
}
?>
