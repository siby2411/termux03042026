<?php
// Fonctions d'authentification de base
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: ../../index.php");
        exit();
    }
}
?>
