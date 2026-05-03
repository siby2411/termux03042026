<?php
// Pas besoin d'afficher le header/footer complet pour un script de traitement simple
include_once __DIR__ . '/../config/db.php';

$database = new Database();
$db = $database->getConnection();

// Récupération de l'ID à supprimer
$id = isset($_GET['id']) ? $_GET['id'] : die('ERREUR: ID de client non spécifié.');

try {
    // Vérification de l'existence de commandes liées (précaution)
    $check_query = "SELECT COUNT(*) FROM Commandes WHERE ClientID = :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $id);
    $check_stmt->execute();
    $count = $check_stmt->fetchColumn();

    if ($count > 0) {
        // Redirection avec message d'erreur si des commandes existent
        header('Location: index.php?action=delete_fail&id=' . $id . '&count=' . $count);
        exit;
    }

    // Requête de suppression
    $query = "DELETE FROM Clients WHERE ClientID = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);

    if ($stmt->execute()) {
        // Succès
        header('Location: index.php?action=deleted');
    } else {
        // Échec
        header('Location: index.php?action=delete_error');
    }

} catch(PDOException $e) {
    // Gestion des erreurs de contraintes d'intégrité (si la vérification échoue ou si une commande cachée existe)
    header('Location: index.php?action=delete_fail&error=' . urlencode($e->getMessage()));
}

exit;
?>
