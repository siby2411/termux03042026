<?php include '../../includes/header.php'; ?>
<div class="card shadow border-0 mx-auto" style="max-width: 700px;">
    <div class="card-header bg-dark text-white py-3">
        <h4 class="mb-0"><i class="fas fa-user-plus"></i> Recrutement Mécanicien</h4>
    </div>
    <div class="card-body p-4">
        <form action="traitement_mecanicien.php" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Spécialité</label>
                    <input type="text" name="specialite" class="form-control" placeholder="ex: Électronique, Moteur Diesel..." required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Salaire Horaire (FCFA)</label>
                    <input type="number" name="salaire_horaire" class="form-control" required>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning w-100 fw-bold">ENREGISTRER LE MÉCANICIEN</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
