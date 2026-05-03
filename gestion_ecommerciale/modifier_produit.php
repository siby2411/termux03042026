<?php
session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$id_produit = intval($_GET['id'] ?? 0);

if ($id_produit === 0) {
    header("Location: crud_produits.php");
    exit();
}

$conn = db_connect();

// Récupérer les données du produit
$sql = "SELECT id_produit, code_produit, designation, prix_unitaire, stock_actuel FROM produits WHERE id_produit = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_produit);
$stmt->execute();
$result = $stmt->get_result();
$produit = $result->fetch_assoc();

if (!$produit) {
    $_SESSION['message'] = "Erreur: Produit non trouvé.";
    header("Location: crud_produits.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Produit: <?php echo htmlspecialchars($produit['designation']); ?></title>
</head>
<body>
    <h1>Modifier le Produit</h1>
    <p><a href="crud_produits.php">Retour à la Liste des Produits</a></p>

    <h2>Produit #<?php echo $produit['code_produit']; ?></h2>
    
    <form action="traitement_produit.php" method="post">
        <input type="hidden" name="action" value="modifier">
        <input type="hidden" name="id_produit" value="<?php echo $produit['id_produit']; ?>">
        
        <label for="code_produit">Code Produit (Lecture Seule):</label>
        <input type="text" value="<?php echo htmlspecialchars($produit['code_produit']); ?>" disabled><br><br>

        <label for="designation">Désignation:</label>
        <input type="text" name="designation" value="<?php echo htmlspecialchars($produit['designation']); ?>" required><br><br>
        
        <label for="prix_unitaire">Prix Unitaire:</label>
        <input type="number" name="prix_unitaire" step="0.01" value="<?php echo $produit['prix_unitaire']; ?>" required><br><br>
        
        <label for="stock_actuel">Stock Actuel (Manuel):</label>
        <input type="number" name="stock_actuel" value="<?php echo $produit['stock_actuel']; ?>"><br><br>
        
        <button type="submit">Enregistrer les Modifications</button>
    </form>
</body>
</html>
