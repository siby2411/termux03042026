<?php
// /form_compte.php
$page_title = "Gestion du Plan Comptable";
include_once 'includes/header.php'; // Inclut le header
// include_once 'config/db.php'; // Connexion DB
?>

<div class="container my-5">
    <div class="card shadow-lg border-info border-3">
        <div class="card-header bg-info text-white fw-bold">
            <i class="fas fa-book-reader me-2"></i> Ajouter/Modifier un Compte Comptable (UEMOA)
        </div>
        <div class="card-body">
            <form action="process_compte.php" method="POST" class="row g-3">
                
                <div class="col-md-4">
                    <label for="codeCompte" class="form-label">Code Compte (Ex: 411, 607)</label>
                    <input type="text" class="form-control" id="codeCompte" name="CodeCompte" maxlength="10" required>
                </div>
                
                <div class="col-md-8">
                    <label for="libelle" class="form-label">Libellé du Compte</label>
                    <input type="text" class="form-control" id="libelle" name="Libelle" required>
                </div>

                <div class="col-md-6">
                    <label for="classe" class="form-label">Classe (1 à 8)</label>
                    <input type="number" class="form-control" id="classe" name="Classe" min="1" max="8" required>
                </div>
                
                <div class="col-md-6">
                    <label for="type" class="form-label">Type de Solde Préféré</label>
                    <select class="form-select" id="type" name="TypeSolde" required>
                        <option value="">Sélectionner...</option>
                        <option value="Debiteur">Débiteur (Actif, Charge)</option>
                        <option value="Crediteur">Créditeur (Passif, Produit)</option>
                    </select>
                </div>

                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-info btn-lg text-white"><i class="fas fa-plus me-2"></i> Enregistrer le Compte</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-muted text-center small">
            <i class="fas fa-layer-group"></i> La cohérence du Plan Comptable est essentielle pour la fiabilité des états financiers.
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
