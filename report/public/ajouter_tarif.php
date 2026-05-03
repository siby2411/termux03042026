<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$page_title = "Tarification - OMEGA";
include 'layout_ecole.php';
?>

<div class="form-centered">
    <div class="card omega-card border-0">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0 text-center"><i class="bi bi-cash-coin"></i> Paramétrage des Tarifs Scolaires</h4>
        </div>
        <div class="card-body p-4">
            <form action="insert_tarif.php" method="POST" class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Sélectionner la Classe</label>
                    <select name="classe_id" class="form-select" required>
                        <?php
                        $res = $conn->query("SELECT id, nom_class FROM classes ORDER BY nom_class");
                        while($row = $res->fetch_assoc()) echo "<option value='".$row['id']."'>".$row['nom_class']."</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Montant Scolarité (CFA/€)</label>
                    <input type="number" name="montant_scolarite" class="form-control" placeholder="Ex: 500000" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Droit d'Inscription</label>
                    <input type="number" name="droit_inscription" class="form-control" placeholder="Ex: 50000" required>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-omega shadow px-5">ENREGISTRER LE TARIF</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
