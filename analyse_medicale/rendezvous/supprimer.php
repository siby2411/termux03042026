<?php
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
    $pdo->beginTransaction();
    // Supprimer les analyses associées
    $pdo->prepare("DELETE FROM rendezvous_analyses WHERE rendezvous_id = ?")->execute([$id]);
    // Supprimer le rendez-vous
    $pdo->prepare("DELETE FROM rendezvous WHERE id = ?")->execute([$id]);
    $pdo->commit();
    $_SESSION['flash'] = "Rendez-vous supprimé.";
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = "Erreur : " . $e->getMessage();
}
header('Location: liste.php');
exit;
