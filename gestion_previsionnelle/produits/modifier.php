<?php
// Inclure le fichier de connexion à la base de données
require_once 'connexion.php';

$produit_id = null;
$message = '';
$produit = null;

// Vérifier si un ID de produit est fourni dans l'URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $produit_id = $_GET['id'];

    try {
        // --- 1. Gérer la soumission du formulaire (Mise à jour) ---
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nom = htmlspecialchars(trim($_POST['nom']));
            $reference = htmlspecialchars(trim($_POST['reference']));
            $prix_vente = floatval($_POST['prix_vente']);
            $prix_achat = floatval($_POST['prix_achat']);
            
            // Validez les données (vérifications minimales)
            if (empty($nom) || empty($reference) || $prix_vente <= 0 || $prix_achat <= 0) {
                $message = '<div class="alert alert-danger">Tous les champs sont obligatoires et les prix doivent être positifs.</div>';
            } else {
                // Requête de mise à jour des informations statiques du produit
                $sql_update = "UPDATE Produits 
                               SET Nom = :nom, Reference = :reference, PrixVente = :prix_vente, PrixAchat = :prix_achat 
                               WHERE ProduitID = :id";
                
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([
                    ':nom' => $nom,
                    ':reference' => $reference,
                    ':prix_vente' => $prix_vente,
                    ':prix_achat' => $prix_achat,
                    ':id' => $produit_id
                ]);

                $message = '<div class="alert alert-success">Le produit a été mis à jour avec succès !</div>';
                // Optionnel : Redirection pour éviter la resoumission du formulaire (Post/Redirect/Get)
                header("Location: modifier_produit.php?id=" . $produit_id . "&success=1");
                exit;
            }
        }

        // --- 2. Récupérer les données actuelles du produit ---
        $sql_select = "SELECT * FROM Produits WHERE ProduitID = :id";
        $stmt_select = $pdo->prepare($sql_select);
        $stmt_select->execute([':id' => $produit_id]);
        $produit = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if (!$produit) {
            $message = '<div class="alert alert-danger">Produit introuvable.</div>';
            $produit_id = null;
        }

    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur de base de données : ' . $e->getMessage() . '</div>';
    }
} else {
    $message = '<div class="alert alert-warning">Aucun ID de produit spécifié.</div>';
}

// Gérer le message de succès après redirection
if (isset($_GET['success']) && $_GET['success'] == 1) {
     $message = '<div class="alert alert-success">Le produit a été mis à jour avec succès !</div>';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Modification du Produit</h1>
        
        <?php echo $message; ?>

        <?php if ($produit): ?>
            <p><strong>ID Produit :</strong> <?php echo htmlspecialchars($produit['ProduitID']); ?></p>
            <p><strong>Stock Actuel :</strong> <?php echo htmlspecialchars($produit['StockActuel']); ?></p>
            <p><strong>CUMP Actuel :</strong> <?php echo number_format($produit['CUMP'], 2, ',', ' '); ?> €</p>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom du Produit</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($produit['Nom']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="reference" class="form-label">Référence</label>
                    <input type="text" class="form-control" id="reference" name="reference" value="<?php echo htmlspecialchars($produit['Reference']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="prix_vente" class="form-label">Prix de Vente (€)</label>
                    <input type="number" step="0.01" class="form-control" id="prix_vente" name="prix_vente" value="<?php echo htmlspecialchars($produit['PrixVente']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="prix_achat" class="form-label">Prix d'Achat Standard (€)</label>
                    <input type="number" step="0.01" class="form-control" id="prix_achat" name="prix_achat" value="<?php echo htmlspecialchars($produit['PrixAchat']); ?>" required>
                    <div class="form-text">Ce prix est utilisé pour les estimations et les achats manuels. Le CUMP est calculé sur la base des Achats réels.</div>
                </div>
                
                <button type="submit" class="btn btn-primary me-2">Enregistrer les Modifications</button>
                <a href="produits.php" class="btn btn-secondary">Retour à la Liste des Produits</a>
            </form>
        <?php else: ?>
             <a href="produits.php" class="btn btn-secondary">Retour à la Liste des Produits</a>
        <?php endif; ?>
    </div>
</body>
</html>
