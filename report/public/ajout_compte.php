<?php
require_once __DIR__ . '/../includes/db.php';
$page_title = "Paramétrage Plan Comptable - OMEGA";
include "layout.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO PLAN_COMPTABLE_UEMOA (compte_id, intitule_compte, classe) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['id'], $_POST['libelle'], substr($_POST['id'], 0, 1)]);
    echo "<div class='alert alert-success form-centered mb-4 shadow-sm'>Compte ajouté au référentiel SYSCOHADA.</div>";
}
?>

<div class="form-centered">
    <div class="card omega-card overflow-hidden">
        <div class="card-header bg-white py-4 border-0">
            <h3 class="text-center fw-bold text-dark mb-0"><i class="bi bi-plus-square-dotted text-primary"></i> Création de Compte</h3>
        </div>
        <div class="card-body p-5">
            <form method="POST" class="row g-4">
                <div class="col-md-4">
                    <label class="form-label">Numéro de Compte</label>
                    <input type="number" name="id" class="form-control" placeholder="Ex: 521" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Intitulé du compte</label>
                    <input type="text" name="libelle" class="form-control" placeholder="Ex: BOA SÉNÉGAL" required>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-omega shadow-lg">
                        <i class="bi bi-save"></i> ENREGISTRER DANS LE PLAN COMPTABLE
                    </button>
                    <a href="admin_dashboard.php" class="btn btn-link text-muted ms-3">Retour Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
