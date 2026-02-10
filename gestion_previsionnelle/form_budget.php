<?php
// /form_budget.php
$page_title = "Saisie des Prévisions Budgétaires";
include_once 'includes/header.php'; // Inclut le header
// include_once 'config/db.php'; // Connexion DB pour la sauvegarde
?>

<div class="container my-5">
    <div class="card shadow-lg border-primary border-3">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-chart-line me-2"></i> Création d'un Poste Budgétaire
        </div>
        <div class="card-body">
            <form action="process_budget.php" method="POST" class="row g-3">
                
                <div class="col-md-6">
                    <label for="annee" class="form-label">Année Budgétaire</label>
                    <input type="number" class="form-control" id="annee" name="Annee" value="<?= date('Y') + 1 ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label for="periode" class="form-label">Période (Mois / Trimestre)</label>
                    <input type="text" class="form-control" id="periode" name="Periode" placeholder="Ex: Janvier, T1, Annuel" required>
                </div>

                <div class="col-md-12">
                    <label for="poste" class="form-label">Poste Budgétaire (Ex: CA Ventes, Charges Salaires)</label>
                    <input type="text" class="form-control" id="poste" name="Poste" required>
                </div>
                
                <div class="col-md-6">
                    <label for="montantPrevu" class="form-label">Montant Prévu (€)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-euro-sign"></i></span>
                        <input type="number" step="0.01" min="0" class="form-control" id="montantPrevu" name="MontantPrevu" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="typeBudget" class="form-label">Type (Produit / Charge / Investissement)</label>
                    <select class="form-select" id="typeBudget" name="TypeBudget" required>
                        <option value="">Sélectionner le type...</option>
                        <option value="Produit">Produit (Revenu)</option>
                        <option value="Charge">Charge (Dépense)</option>
                        <option value="Investissement">Investissement</option>
                        <option value="Financement">Financement</option>
                    </select>
                </div>

                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Enregistrer la Prévision</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-muted text-center small">
            <i class="fas fa-lightbulb"></i> Ces données servent à l'analyse des écarts (Réalisé vs. Prévu).
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
