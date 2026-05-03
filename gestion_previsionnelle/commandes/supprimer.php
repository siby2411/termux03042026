<?php
// commandes/supprimer.php

include_once __DIR__ . '/../config/db.php';
$database = new Database();
$db = $database->getConnection();

// L'ID doit être passé via l'URL (GET)
$id = $_GET['id'] ?? die("ID de commande manquant.");

try {
    // START TRANSACTION pour garantir que tout est supprimé ensemble
    $db->beginTransaction();

    // 1. Suppression des détails (si la clé étrangère DetailsCommande_ibfk_1 n'a pas ON DELETE CASCADE)
    // Si la clé est ON DELETE CASCADE, cette étape est optionnelle mais recommandée.
    $query_details = "DELETE FROM DetailsCommande WHERE CommandeID = ?";
    $stmt_details = $db->prepare($query_details);
    $stmt_details->bindParam(1, $id);
    $stmt_details->execute();

    // 2. Suppression des transactions associées (parce que l'annulation n'a pas été déclenchée)
    // NORMALEMENT, le trigger de la commande annulerait les écritures, puis la commande serait supprimée.
    // Dans un scénario CRUD simple, nous allons supprimer directement :
    $query_transactions = "DELETE FROM Transactions WHERE ReferenceID = ?";
    $stmt_transactions = $db->prepare($query_transactions);
    $stmt_transactions->bindParam(1, $id);
    $stmt_transactions->execute();
    
    // 3. Suppression de la Commande Master
    $query_master = "DELETE FROM Commandes WHERE CommandeID = ?";
    $stmt_master = $db->prepare($query_master);
    $stmt_master->bindParam(1, $id);
    $stmt_master->execute();

    $db->commit();
    header('Location: index.php?action=deleted&id=' . $id);

} catch (PDOException $e) {
    $db->rollBack();
    header('Location: index.php?action=delete_failed&error=' . urlencode($e->getMessage()));
}
exit();
?>
