<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'caissier') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Redirection vers la recherche spatiale pour l'instant
header('Location: recherche_spatiale.php');
exit();
?>
