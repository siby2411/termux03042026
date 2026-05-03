<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('radiologue');

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
try {
    $pdo->prepare("UPDATE comptes_rendus SET signe = 1, date_signature = NOW() WHERE id = ?")->execute([$id]);
    $_SESSION['flash'] = "Compte rendu signé avec succès.";
} catch (PDOException $e) {
    $_SESSION['flash'] = "Erreur : " . $e->getMessage();
}
header('Location: liste.php');
exit;
