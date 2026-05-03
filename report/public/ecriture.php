<?php
require_once __DIR__ . '/../includes/db.php';
$page_title = "Saisie d'Écriture - OMEGA";
include "layout.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['date'], $_POST['libelle'], $_POST['debite'], $_POST['credite'], $_POST['montant'], $_POST['ref']]);
        echo "<div class='alert alert-success alert-dismissible fade show form-centered mb-4' role='alert'>
                <strong>Succès !</strong> L'écriture a été validée et injectée dans le Grand Livre.
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger form-centered mb-4'>Erreur : " . $e->getMessage() . "</div>";
    }
}
?>

<div class="form-centered">
    <div class="card omega-card overflow-hidden">
        <div class="card-header bg-white py-4 border-0">
            <h3 class="text-center fw-bold text-dark mb-0"><i class="bi bi-pencil-square"></i> Journal de Saisie (Norme SYSCOHADA)</h3>
        </div>
        <div class="card-body p-5">
            <form method="POST" class="row g-4">
                <div class="col-md-4">
                    <label class="form-label">Date d'opération</label>
                    <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Référence Pièce</label>
                    <input type="text" name="ref" class="form-control" placeholder="Ex: FACT-2026-001">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Montant (F CFA)</label>
                    <input type="number" name="montant" class="form-control fw-bold text-primary" placeholder="0" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Libellé de l'écriture</label>
                    <input type="text" name="libelle" class="form-control" placeholder="Désignation de l'opération comptable..." required>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4">
                        <label class="form-label text-danger"><i class="bi bi-arrow-down-circle"></i> Débit (Compte)</label>
                        <input type="number" name="debite" class="form-control" placeholder="Ex: 521 (Banque)" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4">
                        <label class="form-label text-success"><i class="bi bi-arrow-up-circle"></i> Crédit (Compte)</label>
                        <input type="number" name="credite" class="form-control" placeholder="Ex: 701 (Ventes)" required>
                    </div>
                </div>
                <div class="col-12 text-center mt-5">
                    <button type="submit" class="btn btn-omega shadow-lg">
                        <i class="bi bi-check2-all"></i> ENREGISTRER L'OPÉRATION
                    </button>
                    <a href="ecriture_list.php" class="btn btn-link text-decoration-none text-muted ms-3">Voir le journal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.min.js"></script>
</body>
</html>
