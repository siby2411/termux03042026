<?php
session_start();
require_once '../../config/database.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE consultations SET statut = 'attente_paiement' WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    
    // Redirection vers le suivi avec un message de succès
    header('Location: suivi.php?msg=cloture_ok');
    exit;
} else {
    // Si l'accès est direct sans POST, on redirige
    header('Location: suivi.php');
    exit;
}
?>
