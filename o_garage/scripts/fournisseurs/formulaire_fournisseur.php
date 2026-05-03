<?php require_once '../../includes/header.php'; ?>
<div class="card shadow col-md-6 mx-auto border-0 rounded-4">
    <div class="card-header bg-dark text-white p-4">
        <h4 class="mb-0"><i class="fas fa-truck me-2"></i>Nouveau Fournisseur</h4>
    </div>
    <div class="card-body p-4">
        <form action="enregistrer_fournisseur.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Nom de l'Entreprise</label>
                <input type="text" name="nom" class="form-control" required placeholder="Ex: CFAO Motors">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Type de Produits</label>
                <select name="type" class="form-select">
                    <option>Pièces d'origine</option>
                    <option>Pneumatiques</option>
                    <option>Lubrifiants</option>
                    <option>Outillage</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Contact (Téléphone)</label>
                <input type="text" name="telephone" class="form-control" required placeholder="77XXXXXXX">
            </div>
            <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold shadow">Enregistrer Fournisseur</button>
        </form>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
