<?php
include_once __DIR__ . '/../config/db.php';
$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? die("ID de fournisseur manquant.");

try {
    $query = "DELETE FROM Fournisseurs WHERE FournisseurID = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);

    if ($stmt->execute()) {
        // Redirection vers la page d'index après suppression réussie
        header('Location: index.php?action=deleted');
    } else {
        // En cas d'échec (ex: contrainte de clé étrangère)
        header('Location: index.php?action=delete_failed');
    }
} catch (PDOException $e) {
    // Si une contrainte de clé étrangère empêche la suppression
    if ($e->getCode() == '23000') {
        header('Location: index.php?action=delete_failed_fk');
    } else {
        // Autre erreur SQL
        header('Location: index.php?action=delete_error');
    }
}
exit();
?><?php 
// Logique de suppression fournisseur (similaire à clients/supprimer.php)
// ...
