<?php
include 'includes/db.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = $_POST['client_id'];
    $produit_id = $_POST['produit_id'];
    $quantite = (int)$_POST['quantite'];

    $p = $pdo->prepare("SELECT prix_unitaire FROM produits WHERE id = ?");
    $p->execute([$produit_id]);
    $prix = $p->fetchColumn();
    $total = $prix * $quantite;

    $stmt = $pdo->prepare("INSERT INTO devis (client_id, produit_id, quantite, total_ht) VALUES (?, ?, ?, ?)");
    $stmt->execute([$client_id, $produit_id, $quantite, $total]);
    $devis_id = $pdo->lastInsertId();
    header("Location: export_devis.php?id=$devis_id");
}

$clients = $pdo->query("SELECT * FROM clients")->fetchAll();
$produits = $pdo->query("SELECT * FROM produits")->fetchAll();
?>

<div class="container-fluid px-4">
    <div class="card border-0 shadow-sm col-md-8 mx-auto">
        <div class="card-header bg-dark text-white p-3">
            <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Éditeur de Devis Professionnel</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Client</label>
                    <select name="client_id" class="form-select" required>
                        <?php foreach($clients as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Produit / Service</label>
                        <select name="produit_id" class="form-select" required>
                            <?php foreach($produits as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['designation']) ?> (<?= $p['prix_unitaire'] ?> €)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Quantité</label>
                        <input type="number" name="quantite" class="form-control" min="1" value="1">
                    </div>
                </div>
                <button type="submit" class="btn btn-dark w-100 mt-3">Générer le Devis PDF</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
