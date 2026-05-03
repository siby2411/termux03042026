<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("UPDATE queue SET status = 'in_progress' WHERE token = ?");
    $stmt->execute([$token]);
    header("Location: ../dashboard/index.php");
    exit;
} else {
    echo "Token non spécifié.";
}
