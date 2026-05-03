<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $designation = $_POST['designation'];
    $prix = $_POST['prix_unitaire'];
    $stock = $_POST['stock_actuel'];

    try {
        $stmt = $pdo->prepare("INSERT INTO produits (designation, prix_unitaire, stock_actuel) VALUES (?, ?, ?)");
        $stmt->execute([$designation, $prix, $stock]);
        header("Location: stock_produits.php?success=Produit ajouté !");
    } catch (PDOException $e) {
        die("❌ Erreur Stock OMEGA : " . $e->getMessage());
    }
}
?>
