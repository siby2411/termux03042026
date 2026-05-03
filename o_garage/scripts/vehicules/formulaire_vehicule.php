<?php include '../../includes/header.php'; ?>
<div class="card shadow border-0 mx-auto" style="max-width: 800px;">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-file-signature"></i> Fiche d'Entrée Véhicule</h4>
    </div>
    <div class="card-body p-4">
        <form action="traitement_vehicule.php" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Immatriculation</label>
                    <input type="text" name="immatriculation" class="form-control" placeholder="ex: DK-1234-A" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Propriétaire (ID Client)</label>
                    <input type="number" name="id_client" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Marque</label>
                    <input type="text" name="marque" class="form-control" placeholder="Toyota, Ford..." required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Modèle</label>
                    <input type="text" name="modele" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold">ENREGISTRER L'ENTRÉE</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
