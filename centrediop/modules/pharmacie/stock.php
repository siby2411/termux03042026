<?php
// ... (Vérification session et DB)
$db = getPDO();

// Mise à jour quantité si formulaire soumis
if(isset($_POST['update_stock'])) {
    $stmt = $db->prepare("UPDATE stock SET quantite = ? WHERE id = ?");
    $stmt->execute([$_POST['quantite'], $_POST['id']]);
}

$stocks = $db->query("SELECT * FROM stock ORDER BY nom_produit ASC")->fetchAll();
?>

<div class="card shadow-sm p-4">
    <h4><i class="fas fa-pills text-success"></i> État du Stock Pharmacie</h4>
    <table class="table table-hover">
        <thead><tr><th>Produit</th><th>Quantité</th><th>État</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach($stocks as $s): ?>
            <tr class="<?= ($s['quantite'] <= $s['seuil_alerte']) ? 'table-danger' : '' ?>">
                <td><?= htmlspecialchars($s['nom_produit']) ?></td>
                <td><?= $s['quantite'] ?></td>
                <td><?= ($s['quantite'] <= $s['seuil_alerte']) ? '⚠️ Seuil Critique' : 'OK' ?></td>
                <td>
                    <form method="POST" class="d-flex">
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                        <input type="number" name="quantite" value="<?= $s['quantite'] ?>" class="form-control form-control-sm" style="width:70px">
                        <button type="submit" name="update_stock" class="btn btn-sm btn-primary ms-1">Mettre à jour</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
