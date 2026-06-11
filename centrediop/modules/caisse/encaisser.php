<?php
session_start();
require_once '../../includes/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $c_id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("UPDATE consultations SET statut = 'payee' WHERE id = ?");
    $stmt->bind_param("i", $c_id);
    
    if ($stmt->execute()) {
        // Redirection vers le reçu qui s'imprime automatiquement
        header("Location: imprimer_recu.php?id=" . $c_id);
        exit();
    }
}
?>
