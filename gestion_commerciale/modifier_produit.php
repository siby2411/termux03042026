<?php
// Fichier : modifier_produit.php
// 1. ACTIVER LE DEBUG (À RETIRER EN PRODUCTION)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'db_connect.php'; 

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$id_produit = intval($_GET['id'] ?? 0);

if ($id_produit === 0) {
    // Si pas d'ID, on redirige
    header("Location: crud_produits.php");
    exit();
}

$conn = db_connect();

// **VÉRIFICATION CRUCIALE DE LA CONNEXION**
if ($conn->connect_error) {
    $_SESSION['message'] = "Erreur de connexion à la base de données: " . $conn->connect_error;
    header("Location: crud_produits.php");
    exit();
}

// Récupérer les données du produit
$sql = "SELECT id_produit, code_produit, designation, prix_unitaire, stock_actuel FROM produits WHERE id_produit = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    // Si la préparation échoue (ex: erreur de syntaxe SQL ou table manquante)
    $_SESSION['message'] = "Erreur de préparation de la requête: " . $conn->error;
    $conn->close();
    header("Location: crud_produits.php");
    exit();
}

$stmt->bind_param("i", $id_produit);
$stmt->execute();
$result = $stmt->get_result();
$produit = $result->fetch_assoc();
$stmt->close(); // Fermer le statement après l'exécution

if (!$produit) {
    $_SESSION['message'] = "Erreur: Produit non trouvé.";
    $conn->close();
    header("Location: crud_produits.php");
    exit();
}

$conn->close();
// Affichage du HTML
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

    <h2>Produit #<?php echo htmlspecialchars($produit['code_produit']); ?></h2>
    
    <form action="traitement_produit.php" method="post">
        <input type="hidden" name="action" value="modifier">
        <input type="hidden" name="id_produit" value="<?php echo htmlspecialchars($produit['id_produit']); ?>">
        
        <label for="code_produit">Code Produit (Lecture Seule):</label>
        <input type="text" value="<?php echo htmlspecialchars($produit['code_produit']); ?>" disabled><br><br>

        <label for="designation">Désignation:</label>
        <input type="text" name="designation" value="<?php echo htmlspecialchars($produit['designation']); ?>" required><br><br>
        
        <label for="prix_unitaire">Prix Unitaire:</label>
        <input type="number" name="prix_unitaire" step="0.01" value="<?php echo number_format($produit['prix_unitaire'], 2, '.', ''); ?>" required><br><br>
        
        <label for="stock_actuel">Stock Actuel (Manuel):</label>
        <input type="number" name="stock_actuel" value="<?php echo htmlspecialchars($produit['stock_actuel']); ?>"><br><br>
        
        <button type="submit">Enregistrer les Modifications</button>
    </form>
</body>
</html>
