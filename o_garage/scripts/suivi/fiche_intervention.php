<?php include '../../includes/header.php'; ?>
<div class="card shadow-lg border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">FICHE TECHNIQUE D'ENTRÉE & ÉTAT DES LIEUX</h4>
        <span class="badge bg-warning text-dark">N° REF: <?= date('Ymd') ?>-TEMP</span>
    </div>
    <div class="card-body">
        <form action="../diagnostics/traitement_diagnostic.php" method="POST">
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="fw-bold">Kilométrage au compteur</label>
                    <input type="number" name="kilometrage" class="form-control" placeholder="ex: 125000" required>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">Niveau Carburant</label>
                    <select name="carburant" class="form-select">
                        <option>Réserve</option>
                        <option>1/4</option>
                        <option>1/2</option>
                        <option>3/4</option>
                        <option>Plein</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">Urgence</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="urgence" id="u1" value="Normale" checked>
                        <label class="btn btn-outline-success" for="u1">Normale</label>
                        <input type="radio" class="btn-check" name="urgence" id="u2" value="Haute">
                        <label class="btn btn-outline-danger" for="u2">Haute</label>
                    </div>
                </div>
            </div>

            <h5 class="border-bottom pb-2"><i class="fas fa-check-circle text-primary"></i> Check-list Sécurité Entrée</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="roue_secours"> <label>Roue de secours</label></div></div>
                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="cric"> <label>Cric / Manivelle</label></div></div>
                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="extincteur"> <label>Extincteur</label></div></div>
                <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="autoradio"> <label>Auto-radio</label></div></div>
            </div>

            <div class="form-group mb-4">
                <label class="fw-bold">Observations Carrosserie (Rayures, chocs...)</label>
                <textarea name="symptomes" class="form-control" rows="3" placeholder="Décrivez l'état extérieur du véhicule..."></textarea>
            </div>

            <div class="bg-light p-3 rounded mb-4">
                <label class="fw-bold">Demande spécifique du client</label>
                <textarea name="constat_technique" class="form-control" rows="2" placeholder="Ex: Bruit au freinage, révision générale..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 shadow">VALIDER L'ENTRÉE ET GÉNÉRER L'ORDRE DE RÉPARATION</button>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
