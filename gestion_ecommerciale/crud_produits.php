<?php
session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$conn = db_connect();

// --- LOGIQUE D'AFFICHAGE (READ) ---
$produits = [];
$result = $conn->query("SELECT id_produit, code_produit, designation, prix_unitaire, stock_actuel FROM produits ORDER BY id_produit DESC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produits[] = $row;
    }
}

$conn->close();

// Message de succès/erreur après une action CUD
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Produits</title>
</head>
<body>
    <h1>Gestion des Produits</h1>
    <p><a href="dashboard.php">Retour au Tableau de Bord</a></p>

    <?php if ($message): ?>
        <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h2>Ajouter un Nouveau Produit</h2>
    <form action="traitement_produit.php" method="post">
        <input type="hidden" name="action" value="ajouter">
        
        <label for="designation">Désignation:</label>
        <input type="text" name="designation" required><br><br>
        
        <label for="prix_unitaire">Prix Unitaire:</label>
        <input type="number" name="prix_unitaire" step="0.01" required><br><br>
        
        <label for="stock_initial">Stock Initial (Optionnel):</label>
        <input type="number" name="stock_initial" value="0"><br><br>
        
        <button type="submit">Ajouter Produit</button>
    </form>

    <hr>
    
    <h2>Liste des Produits (<?php echo count($produits); ?>)</h2>
    <?php if (count($produits) > 0): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code Produit</th>
                <th>Désignation</th>
                <th>Prix Unitaire</th>
                <th>Stock Actuel</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $p): ?>
            <tr>
                <td><?php echo $p['id_produit']; ?></td>
                <td><?php echo htmlspecialchars($p['code_produit']); ?></td>
                <td><?php echo htmlspecialchars($p['designation']); ?></td>
                <td><?php echo number_format($p['prix_unitaire'], 2, ',', ' '); ?></td>
                <td><?php echo $p['stock_actuel']; ?></td>
                <td>
                    <a href="modifier_produit.php?id=<?php echo $p['id_produit']; ?>">Modifier</a> |
                    <a href="traitement_produit.php?action=supprimer&id=<?php echo $p['id_produit']; ?>" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucun produit enregistré pour le moment.</p>
    <?php endif; ?>
</body>
</html>
