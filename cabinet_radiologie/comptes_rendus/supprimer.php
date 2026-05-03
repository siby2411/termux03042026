<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
try {
    $pdo->prepare("DELETE FROM comptes_rendus WHERE id = ?")->execute([$id]);
    $_SESSION['flash'] = "Compte rendu supprimé.";
} catch (PDOException $e) {
    $_SESSION['flash'] = "Erreur : " . $e->getMessage();
}
header('Location: liste.php');
exit;
