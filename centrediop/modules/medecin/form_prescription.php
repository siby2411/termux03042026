<div class="card shadow-sm p-4 mt-3">
    <h5><i class="fas fa-pills text-success"></i> Nouvelle Prescription</h5>
    <form action="/modules/medecin/save_traitement.php" method="POST">
        <input type="hidden" name="patient_id" value="<?= $_GET['id'] ?>">
        <div class="mb-3">
            <label>Sélectionner le médicament</label>
            <select name="medicament" class="form-select" required>
                <?php foreach($db->query("SELECT nom_produit FROM stock") as $p): ?>
                    <option value="<?= $p['nom_produit'] ?>"><?= $p['nom_produit'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <textarea name="posologie" class="form-control" placeholder="Posologie (ex: 1 matin et soir)..." required></textarea>
        </div>
        <button type="submit" class="btn btn-success w-100">Valider la prescription</button>
    </form>
</div>
