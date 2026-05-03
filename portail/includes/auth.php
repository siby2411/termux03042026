<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_type'] !== $role && $_SESSION['user_type'] !== 'admin') {
        die('Accès non autorisé.');
    }
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUserType() {
    return $_SESSION['user_type'] ?? null;
}

function currentUserName() {
    return $_SESSION['user_name'] ?? '';
}
?>
