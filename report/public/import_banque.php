<?php
require_once "../includes/db.php";
include "header.php";
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">📥 Importer un Relevé Bancaire (Fictif)</h3>
        </div>
        <div class="card-body">
            <form action="traitement_import.php" method="POST">
                <div id="lignes-banque">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date[]" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Libellé Bancaire</label>
                            <input type="text" name="libelle[]" class="form-control" placeholder="Ex: VIR CLIENT OMEGA">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Débit (-)</label>
                            <input type="number" name="debit[]" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Crédit (+)</label>
                            <input type="number" name="credit[]" class="form-control" step="0.01" value="0">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Enregistrer sur le Relevé</button>
                <a href="rapprochement.php" class="btn btn-secondary">Aller au Rapprochement</a>
            </form>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
