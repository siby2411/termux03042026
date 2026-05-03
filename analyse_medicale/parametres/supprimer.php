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

// Récupérer analyse_id pour redirection
$stmt = $pdo->prepare("SELECT analyse_id FROM parametres_analyse WHERE id = ?");
$stmt->execute([$id]);
$analyse_id = $stmt->fetchColumn();

try {
    $stmt = $pdo->prepare("DELETE FROM parametres_analyse WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = "Paramètre supprimé.";
} catch (PDOException $e) {
    $_SESSION['flash'] = "Erreur : " . $e->getMessage();
}
header('Location: liste.php?analyse_id=' . $analyse_id);
exit;
