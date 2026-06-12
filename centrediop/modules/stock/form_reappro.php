<div class="card shadow-sm p-4 mt-3">
    <h5><i class="fas fa-plus-circle text-warning"></i> Réapprovisionner Stock</h5>
    <form action="/modules/stock/save_stock.php" method="POST" class="row g-2">
        <div class="col-md-6">
            <select name="id" class="form-select">
                <?php foreach($db->query("SELECT id, nom_produit FROM stock") as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['nom_produit'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" name="quantite_ajoutee" class="form-control" placeholder="Qté" required>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-warning w-100">Ajouter</button>
        </div>
    </form>
</div>
