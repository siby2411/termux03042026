<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function loginUser($id, $nom, $role) {
    $_SESSION['admin_id'] = $id;
    $_SESSION['admin_nom'] = $nom;
    $_SESSION['admin_role'] = $role;
}

function logoutUser() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function isAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

function isManager() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'manager';
}

function currentUserId() {
    return $_SESSION['admin_id'] ?? null;
}

function currentUserName() {
    return $_SESSION['admin_nom'] ?? '';
}
?>
