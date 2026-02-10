<?php
// /form_flux.php
$page_title = "Saisie d'un Flux de Trésorerie (TFT)";
include_once 'includes/header.php'; // Inclusion du header
// include_once 'config/db.php'; // Connexion DB pour la gestion des comptes

// Simuler la récupération des comptes de trésorerie (Banque, Caisse) et comptes impactés (101, 200, 607...)
$comptes_flux = [
    '512' => 'Banque Principale', 
    '530' => 'Caisse', 
    '101' => 'Capital Social', 
    '164' => 'Emprunts Long Terme',
    '200' => 'Immobilisations Incorporelles/Corporelles',
    '411' => 'Clients (Encaissements)',
    '401' => 'Fournisseurs (Décaissements)'
]; 
?>

<div class="container my-5">
    <div class="card shadow-lg border-info border-3">
        <div class="card-header bg-info text-white fw-bold">
            <i class="fas fa-exchange-alt me-2"></i> Enregistrement d'un Mouvement de Flux de Trésorerie
        </div>
        <div class="card-body">
            <form action="process_flux.php" method="POST" class="row g-3">
                
                <div class="col-md-6">
                    <label for="dateComptable" class="form-label">Date du Flux (Date de Valeur)</label>
                    <input type="date" class="form-control" id="dateComptable" name="DateComptable" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label for="typeFlux" class="form-label fw-bold">Type de Flux (Crucial pour le TFT)</label>
                    <select class="form-select" id="typeFlux" name="TypeFlux" required>
                        <option value="">Sélectionnez la catégorie...</option>
                        <option value="Exploitation">Flux d'Exploitation (FTE)</option>
                        <option value="Investissement">Flux d'Investissement (FTI)</option>
                        <option value="Financement">Flux de Financement (FTF)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="montant" class="form-label">Montant (€)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-euro-sign"></i></span>
                        <input type="number" step="0.01" class="form-control" id="montant" name="Montant" placeholder="Ex: 15000 (Encaissement) ou -500 (Décaissement)" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="codeCompte" class="form-label">Compte Comptable Impacté (GL)</label>
                    <select class="form-select" id="codeCompte" name="CodeCompte" required>
                        <option value="">Sélectionnez le compte d'origine/destination...</option>
                        <?php foreach ($comptes_flux as $code => $libelle): ?>
                            <option value="<?= $code ?>"><?= $code ?> - <?= $libelle ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <label for="libelle" class="form-label">Libellé / Justificatif du Flux</label>
                    <textarea class="form-control" id="libelle" name="Libelle" rows="2" required></textarea>
                </div>

                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-info btn-lg text-white"><i class="fas fa-arrow-circle-right me-2"></i> Enregistrer le Flux</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-muted text-center small">
            <i class="fas fa-info-circle"></i> Le solde de la Trésorerie Nette est la somme des flux passés.
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
