<?php
include 'includes/db.php';
include 'includes/header.php';
include_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pid = $_POST['produit_id'];
    $qte = (int)$_POST['quantite'];
    $bl = $_POST['num_bl'];
    $fournisseur = $_POST['fournisseur'];

    try {
        $pdo->beginTransaction();

        // 1. Enregistrer la réception
        $ins = $pdo->prepare("INSERT INTO receptions (produit_id, quantite_recue, num_bl, fournisseur_nom) VALUES (?, ?, ?, ?)");
        $ins->execute([$pid, $qte, $bl, $fournisseur]);

        // 2. Mettre à jour le stock réel
        $upd = $pdo->prepare("UPDATE produits SET stock_actuel = stock_actuel + ? WHERE id = ?");
        $upd->execute([$qte, $pid]);

        // 3. Log du mouvement pour l'historique
        $log = $pdo->prepare("INSERT INTO stock_logs (produit_id, quantite, type) VALUES (?, ?, 'entree')");
        $log->execute([$pid, $qte]);

        logAction($pdo, 'RECEPTION_APPRO', 'Réception de ' . $qte . ' unités pour le produit ID ' . $pid);
        $pdo->commit();
        echo "<div class='alert alert-success shadow-sm'><b>Entrée validée !</b> $qte unités ajoutées au stock via BL: $bl.</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
    }
}

$produits = $pdo->query("SELECT * FROM produits ORDER BY designation ASC")->fetchAll();
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-truck-loading me-2"></i>Réception de Marchandise</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Produit à réceptionner</label>
                            <select name="produit_id" class="form-select select2" required>
                                <?php foreach($produits as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= $p['designation'] ?> (Actuel: <?= $p['stock_actuel'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantité Reçue</label>
                                <input type="number" name="quantite" class="form-control" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">N° Bon de Livraison</label>
                                <input type="text" name="num_bl" class="form-control" placeholder="ex: BL-789-2026" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fournisseur / Expéditeur</label>
                            <input type="text" name="fournisseur" class="form-control" placeholder="Nom du fournisseur" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">Valider l'entrée en stock</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Dernières réceptions (Traçabilité)</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Produit</th><th>Qté</th><th>BL</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $last = $pdo->query("SELECT r.*, p.designation FROM receptions r JOIN produits p ON r.produit_id = p.id ORDER BY r.date_reception DESC LIMIT 5")->fetchAll();
                            foreach($last as $l): ?>
                            <tr>
                                <td><?= date('d/m H:i', strtotime($l['date_reception'])) ?></td>
                                <td><?= $l['designation'] ?></td>
                                <td class="text-success fw-bold">+<?= $l['quantite_recue'] ?></td>
                                <td><small><?= $l['num_bl'] ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
