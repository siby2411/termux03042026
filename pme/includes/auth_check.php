<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Fonction pour restreindre l'accès par service
function checkService($allowed_service_id) {
    if ($_SESSION['service_id'] != $allowed_service_id && $_SESSION['role'] != 'Administrateur') {
        die("<div class='alert alert-danger'>Accès refusé : ce module appartient au service " . $_SESSION['service_nom'] . "</div>");
    }
}
?>
