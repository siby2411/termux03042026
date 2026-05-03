<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();
?>

<div class="card shadow-lg border-0">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-file-medical me-2"></i>RÉCEPTION & MISE À JOUR KILOMÉTRAGE</h5>
    </div>
    <div class="card-body">
        <form action="save_fiche.php" method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="fw-bold text-danger">Kilométrage Actuel (Indispensable pour CRM)</label>
                    <input type="number" name="km_actuel" class="form-control border-danger" placeholder="Ex: 145000" required>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">Véhicule</label>
                    <select name="id_vehicule" class="form-select" required>
                        <?php 
                        $v = $db->query("SELECT id_vehicule, immatriculation, marque FROM vehicules");
                        while($row = $v->fetch()) echo "<option value='{$row['id_vehicule']}'>{$row['immatriculation']} - {$row['marque']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">Complexité</label>
                    <select name="complexite" class="form-select">
                        <option value="Faible">Faible</option>
                        <option value="Moyenne" selected>Moyenne</option>
                        <option value="Haute">Haute</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="fw-bold text-primary">Mécanicien Responsable</label>
                    <select name="id_mec_1" class="form-select" required>
                        <?php 
                        $m = $db->query("SELECT id_personnel, nom_complet FROM personnel");
                        while($row = $m->fetch()) echo "<option value='{$row['id_personnel']}'>{$row['nom_complet']}</option>";
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="fw-bold text-success">Coût MO Estimé (F)</label>
                    <input type="number" name="cout_main_doeuvre" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="fw-bold">Diagnostic / Travaux à faire</label>
                    <textarea name="diagnostic_technique" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary btn-lg">Ouvrir l'Ordre de Réparation</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
