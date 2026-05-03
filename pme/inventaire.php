<?php
include 'includes/db.php';
include 'includes/header.php';

// Traitement de la mise à jour massive
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['stocks'])) {
    try {
        $pdo->beginTransaction();
        foreach ($_POST['stocks'] as $id => $quantite) {
            // On récupère l'ancien stock pour le log
            $old = $pdo->prepare("SELECT stock_actuel FROM produits WHERE id = ?");
            $old->execute([$id]);
            $ancien_stock = $old->fetchColumn();

            if ($ancien_stock != $quantite) {
                // Mise à jour
                $update = $pdo->prepare("UPDATE produits SET stock_actuel = ? WHERE id = ?");
                $update->execute([$quantite, $id]);
                
                // Log du mouvement d'inventaire
                $diff = $quantite - $ancien_stock;
                $type = ($diff > 0) ? 'entree' : 'sortie';
                $log = $pdo->prepare("INSERT INTO stock_logs (produit_id, quantite, type) VALUES (?, ?, ?)");
                $log->execute([$id, abs($diff), $type]);
            }
        }
        $pdo->commit();
        echo "<div class='alert alert-success shadow-sm'><i class='fas fa-check-double me-2'></i>Inventaire mis à jour avec succès et mouvements tracés.</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
    }
}

$produits = $pdo->query("SELECT * FROM produits ORDER BY designation ASC")->fetchAll();
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-clipboard-list text-primary me-2"></i>Inventaire de Fin de Mois</h2>
        <span class="badge bg-secondary">Mode : Mise à jour rapide</span>
    </div>

    <form method="POST">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Visuel</th>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th style="width: 200px;">Stock Physique Réel</th>
                            <th>Seuil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($produits as $p): ?>
                        <tr>
                            <td><img src="uploads/produits/<?= $p['image_path'] ?>" width="40" class="rounded shadow-sm"></td>
                            <td><small class="text-muted"><?= $p['ref_interne'] ?></small></td>
                            <td class="fw-bold"><?= $p['designation'] ?></td>
                            <td>
                                <input type="number" name="stocks[<?= $p['id'] ?>]" 
                                       class="form-control form-control-sm <?= $p['stock_actuel'] <= $p['seuil_alerte'] ? 'border-danger' : '' ?>" 
                                       value="<?= $p['stock_actuel'] ?>">
                            </td>
                            <td><span class="badge bg-light text-dark"><?= $p['seuil_alerte'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white p-3">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                    <i class="fas fa-save me-2"></i>Valider et Enregistrer l'Inventaire Complet
                </button>
            </div>
        </div>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
