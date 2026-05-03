<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();

// Récupérer la chambre avant suppression
$stmt = $pdo->prepare("SELECT chambre_id FROM reservations WHERE id = ?");
$stmt->execute([$id]);
$chambre_id = $stmt->fetchColumn();

try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM reservations WHERE id = ?")->execute([$id]);
    // Libérer la chambre
    $pdo->prepare("UPDATE chambres SET statut = 'disponible' WHERE id = ? AND statut = 'reserve'")->execute([$chambre_id]);
    $pdo->commit();
    $_SESSION['flash'] = "Réservation supprimée.";
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = "Erreur : " . $e->getMessage();
}
header('Location: liste.php');
exit;
