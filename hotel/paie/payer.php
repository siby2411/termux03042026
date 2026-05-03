<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: liste.php');
    exit;
}

$pdo = getPDO();
try {
    $stmt = $pdo->prepare("UPDATE paie SET paye = 1, date_paiement = CURDATE() WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = "Paie marquée comme payée.";
} catch (PDOException $e) {
    $_SESSION['flash'] = "Erreur: " . $e->getMessage();
}
header('Location: liste.php');
exit;
