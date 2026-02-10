<?php
// /form_ecriture.php
$page_title = "Saisie d'Écriture au Grand Livre";
include_once 'includes/header.php'; // Inclut le header (design, CSS...)
include_once 'config/db.php'; 
// Connexion DB et logique de traitement...

// Simuler la récupération des comptes pour le menu déroulant
$comptes = [
    '411' => 'Clients', 
    '512' => 'Banque', 
    '607' => 'Achats Marchandises', 
    '707' => 'Ventes Marchandises'
]; 
?>

<div class="container my-5">
    <div class="card shadow-lg border-dark border-3">
        <div class="card-header bg-dark text-white fw-bold">
            <i class="fas fa-file-invoice me-2"></i> Nouvelle Écriture Comptable
        </div>
        <div class="card-body">
            <form action="process_ecriture.php" method="POST" class="row g-3">
                
                <div class="col-md-6">
                    <label for="dateComptable" class="form-label">Date Comptable</label>
                    <input type="date" class="form-control" id="dateComptable" name="DateComptable" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label for="montant" class="form-label">Montant (Débit = Crédit)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-euro-sign"></i></span>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="montant" name="Montant" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="compteDebiteur" class="form-label">Compte Débiteur (Emploi / Charge)</label>
                    <select class="form-select" id="compteDebiteur" name="CompteDebiteur" required>
                        <option value="">Sélectionnez un compte...</option>
                        <?php foreach ($comptes as $code => $libelle): ?>
                            <option value="<?= $code ?>"><?= $code ?> - <?= $libelle ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="compteCrediteur" class="form-label">Compte Créditeur (Ressource / Produit)</label>
                    <select class="form-select" id="compteCrediteur" name="CompteCrediteur" required>
                        <option value="">Sélectionnez un compte...</option>
                        <?php foreach ($comptes as $code => $libelle): ?>
                            <option value="<?= $code ?>"><?= $code ?> - <?= $libelle ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label for="libelle" class="form-label">Libellé / Description de l'opération</label>
                    <textarea class="form-control" id="libelle" name="Libelle" rows="2" required></textarea>
                </div>

                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Enregistrer l'Écriture</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-muted text-center small">
            <i class="fas fa-balance-scale"></i> Assurez-vous que l'équilibre Débit = Crédit est respecté pour chaque écriture.
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
