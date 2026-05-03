<?php include '../../includes/header.php'; ?>
<div class="card shadow border-0 mx-auto" style="max-width: 800px;">
    <div class="card-header bg-dark text-white py-3">
        <h4 class="mb-0"><i class="fas fa-box-open"></i> Gestion de Référence Pièce</h4>
    </div>
    <div class="card-body p-4">
        <form action="traitement_stock.php" method="POST">
            <input type="hidden" name="action" value="ajouter">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Référence</label>
                    <input type="text" name="reference" class="form-control" placeholder="REF-000" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Désignation (Libellé)</label>
                    <input type="text" name="libelle" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Prix Achat (FCFA)</label>
                    <input type="number" name="prix_achat" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Prix Vente (FCFA)</label>
                    <input type="number" name="prix_vente" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quantité Initiale</label>
                    <input type="number" name="stock_actuel" class="form-control" value="0" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Seuil d'Alerte</label>
                    <input type="number" name="seuil_alerte" class="form-control" value="5" required>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">ENREGISTRER EN STOCK</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
