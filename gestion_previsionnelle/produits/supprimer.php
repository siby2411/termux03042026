<?php
// produits/supprimer.php
include_once __DIR__ . '/../config/db.php';
$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? die("ID de produit manquant.");

try {
    $query = "DELETE FROM Produits WHERE ProduitID = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);

    if ($stmt->execute()) {
        header('Location: index.php?action=deleted');
    } else {
        header('Location: index.php?action=delete_failed');
    }
} catch (PDOException $e) {
    // Code 23000 = Violation de contrainte (clé étrangère dans DetailsCommande ou DetailAchat)
    if ($e->getCode() == '23000') {
        header('Location: index.php?action=delete_failed_fk');
    } else {
        header('Location: index.php?action=delete_error');
    }
}
exit();
?>
