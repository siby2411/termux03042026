<?php
include_once 'includes/functions.php';
include 'includes/db.php';
include 'includes/header.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_nom = $_POST['client_nom'];
    $produit_id = $_POST['produit_id'];
    $quantite = (int)$_POST['quantite'];

    try {
        $pdo->beginTransaction();

        // 1. Vérification du stock en temps réel
        $stmtStock = $pdo->prepare("SELECT designation, stock_actuel, prix_unitaire FROM produits WHERE id = ? FOR UPDATE");
        $stmtStock->execute([$produit_id]);
        $produit = $stmtStock->fetch();

        if ($produit && $produit['stock_actuel'] >= $quantite) {
            $total_ht = $produit['prix_unitaire'] * $quantite;

            // 2. Création de la commande
            $stmtCmd = $pdo->prepare("INSERT INTO commandes (client_nom, total_ht, etat, date_commande) VALUES (?, ?, 'validee', NOW())");
            $stmtCmd->execute([$client_nom, $total_ht]);
            $commande_id = $pdo->lastInsertId();

            // 3. Décrémentation automatique du stock (Innovation Flux)
            $newStock = $produit['stock_actuel'] - $quantite;
            $updateStock = $pdo->prepare("UPDATE produits SET stock_actuel = ? WHERE id = ?");
            verifierAlerteStock($pdo, $produit_id);
            $updateStock->execute([$newStock, $produit_id]);

            // 4. Log du mouvement de stock pour l'audit
            $log = $pdo->prepare("INSERT INTO stock_logs (produit_id, quantite, type) VALUES (?, ?, 'sortie')");
            $log->execute([$produit_id, $quantite]);

            $pdo->commit();
            $message = "<div class='alert alert-success shadow-sm'><b>Succès !</b> Commande #$commande_id créée. Stock mis à jour pour {$produit['designation']}.</div>";
        } else {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger shadow-sm'><b>Erreur Stock :</b> Quantité insuffisante pour {$produit['designation']} (Disponible: {$produit['stock_actuel']}).</div>";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-warning'>Erreur système : " . $e->getMessage() . "</div>";
    }
}

$produits = $pdo->query("SELECT * FROM produits WHERE stock_actuel > 0")->fetchAll();
$clients = $pdo->query("SELECT * FROM clients")->fetchAll();
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-cart-plus text-primary me-2"></i>Nouvelle Commande Intelligente</h2>
        <a href="logistique.php" class="btn btn-outline-secondary btn-sm">Voir Expéditions</a>
    </div>

    <?= $message ?>

    <div class="row">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Sélectionner le Client</label>
                            <select name="client_nom" class="form-select form-select-lg" required>
                                <option value="">--- Client ---</option>
                                <?php foreach($clients as $c): ?>
                                    <option value="<?= htmlspecialchars($c['nom']) ?>"><?= htmlspecialchars($c['nom']) ?> (<?= $c['ville'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label class="form-label fw-bold">Produit en Stock</label>
                                <select name="produit_id" class="form-select" required>
                                    <?php foreach($produits as $p): ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['designation']) ?> - <?= $p['prix_unitaire'] ?> € (Dispo: <?= $p['stock_actuel'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-bold">Quantité</label>
                                <input type="number" name="quantite" class="form-control" min="1" value="1" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-check-circle me-2"></i>Valider et déduire du stock
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="card-title fw-bold"><i class="fas fa-info-circle me-2"></i>Aide au remplissage</h5>
                    <p class="small text-muted">Ce module effectue une transaction SQL sécurisée. En cas de validation :</p>
                    <ul class="small">
                        <li>Le stock est décrémenté immédiatement.</li>
                        <li>Un bon d'expédition est créé en logistique.</li>
                        <li>La commande devient prête pour la facturation.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
