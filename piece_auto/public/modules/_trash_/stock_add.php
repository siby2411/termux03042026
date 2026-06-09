<?php
session_start();
require __DIR__ . '/../config/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produit = trim($_POST['produit'] ?? '');
    $quantite = floatval($_POST['quantite'] ?? 0);
    $prix_unitaire = floatval($_POST['prix_unitaire'] ?? 0);
    $date_entree = $_POST['date_entree'] ?? date('Y-m-d');

    if ($produit && $quantite > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO STOCK (produit, quantite, prix_unitaire, date_entree) VALUES (?, ?, ?, ?)");
            $stmt->execute([$produit, $quantite, $prix_unitaire, $date_entree]);
            $message = "✅ Article ajouté au stock.";
        } catch (PDOException $e) {
            $message = "❌ Erreur ajout stock : " . $e->getMessage();
        }
    } else {
        $message = "⚠️ Produit et quantité obligatoires.";
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Ajouter au Stock</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f4f6f9;padding:20px">
<div class="container">
    <div class="card p-3">
        <h4>Ajouter un mouvement stock (entrée)</h4>
        <?php if($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-2">
                <label>Produit (nom)</label>
                <input type="text" name="produit" class="form-control" required>
            </div>
            <div class="mb-2 row">
                <div class="col">
                    <label>Quantité</label>
                    <input type="number" step="0.01" name="quantite" class="form-control" required>
                </div>
                <div class="col">
                    <label>Prix unitaire</label>
                    <input type="number" step="0.01" name="prix_unitaire" class="form-control" required>
                </div>
            </div>
            <div class="mb-2">
                <label>Date</label>
                <input type="date" name="date_entree" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <button class="btn btn-success">Ajouter au stock</button>
            <a href="stock.php" class="btn btn-secondary">Retour liste stock</a>
        </form>
    </div>
</div>
</body>
</html>




