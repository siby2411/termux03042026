<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

// Nettoyer les anciennes entrées
$pdo->exec("DELETE FROM file_attente WHERE statut = 'termine' AND cree_a < DATE_SUB(NOW(), INTERVAL 7 DAY)");
$pdo->exec("DELETE FROM pointages WHERE date_pointage < DATE_SUB(CURDATE(), INTERVAL 30 DAY)");

// Optimiser les tables
$pdo->exec("OPTIMIZE TABLE file_attente");
$pdo->exec("OPTIMIZE TABLE pointages");

echo json_encode(['success' => true]);
?>
