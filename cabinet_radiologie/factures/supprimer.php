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
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM paiements WHERE facture_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM factures WHERE id = ?")->execute([$id]);
    $pdo->commit();
    $_SESSION['flash'] = "Facture supprimée.";
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = "Erreur : " . $e->getMessage();
}
header('Location: liste.php');
exit;
