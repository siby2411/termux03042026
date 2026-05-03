<?php require_once '../../includes/header.php'; ?>
<div class="container mt-4">
    <div class="card shadow border-0 mx-auto" style="max-width: 500px;">
        <div class="card-header bg-primary text-white text-center fw-bold">
            RECRUTEMENT MÉCANICIEN
        </div>
        <div class="card-body">
            <form action="traitement_mecanicien.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Nom Complet</label>
                    <input type="text" name="nom_complet" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-primary fw-bold">Spécialité</label>
                    <select name="id_spec" class="form-select border-primary" required>
                        <option value="">-- Choisir une spécialité --</option>
                        <?php 
                        $specs = $db->query("SELECT * FROM specialites ORDER BY nom_specialite ASC");
                        while($s = $specs->fetch()) echo "<option value='{$s['id_spec']}'>{$s['nom_specialite']}</option>";
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" placeholder="77..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Salaire Horaire (FCFA)</label>
                    <input type="number" name="salaire_horaire" class="form-control" value="2500">
                </div>

                <button type="submit" class="btn btn-dark w-100 fw-bold">ENREGISTRER LE MÉCANICIEN</button>
            </form>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
