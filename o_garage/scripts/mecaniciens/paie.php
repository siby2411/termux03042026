<?php require_once '../../includes/header.php'; ?>

<div class="card shadow-lg border-0 col-md-6 mx-auto rounded-4">
    <div class="card-header bg-dark text-white p-4">
        <h4 class="mb-0"><i class="fas fa-wallet me-2 text-warning"></i>Gestion de la Paie Technique</h4>
    </div>
    <div class="card-body p-4">
        <form action="generer_bulletin.php" method="GET">
            <div class="mb-4">
                <label class="form-label fw-bold">Sélectionner le Mécanicien (Code)</label>
                <select name="id" class="form-select form-select-lg" required>
                    <option value="">-- Sélectionner --</option>
                    <?php 
                    $res = $db->query("SELECT id_personnel, code_interne, nom_complet FROM personnel");
                    while($m = $res->fetch()) {
                        echo "<option value='{$m['id_personnel']}'>[{$m['code_interne']}] {$m['nom_complet']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold">Période de calcul</label>
                <input type="month" name="periode" class="form-control form-control-lg" value="<?= date('Y-m') ?>">
            </div>

            <button type="submit" class="btn btn-warning btn-lg w-100 shadow-sm fw-bold">
                <i class="fas fa-file-invoice-dollar me-2"></i>Générer le Bulletin PDF
            </button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
