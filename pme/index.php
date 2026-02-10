<?php
// include 'includes/auth_check.php'; // À créer pour vérifier la session
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="fw-bold text-dark">Tableau de Pilotage</h2>
        <p class="text-muted">Vue d'ensemble des activités de la PME.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100 card-dashboard border-primary">
            <div class="card-body text-center">
                <i class="fas fa-file-invoice-dollar fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Facturation & Commandes</h5>
                <p class="card-text">Suivi des devis, bons de commande et factures clients.</p>
                <a href="facturation.php" class="btn btn-primary">Gérer</a>
            </div>
            <div class="card-footer bg-transparent border-primary">
                <small class="text-muted">État financier en direct</small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 card-dashboard border-success">
            <div class="card-body text-center">
                <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                <h5 class="card-title">Logistique & Stock</h5>
                <p class="card-text">Gestion des entrées/sorties et alertes de stock.</p>
                <a href="stock.php" class="btn btn-success">Accéder</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 card-dashboard border-warning">
            <div class="card-body text-center">
                <i class="fas fa-cloud-upload-alt fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Partage Documents</h5>
                <p class="card-text">Uploads et partage de fichiers entre services (Marketing, RH...).</p>
                <a href="documents.php" class="btn btn-warning text-dark">Mes Fichiers</a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100 card-dashboard border-info">
            <div class="card-body d-flex align-items-center">
                <div class="me-3">
                     <i class="fas fa-users-cog fa-3x text-info"></i>
                </div>
                <div>
                    <h5 class="card-title">Administration & RH</h5>
                    <p class="card-text mb-0">Gestion des collaborateurs, rôles et permissions d'accès.</p>
                    <a href="admin_users.php" class="btn btn-sm btn-outline-info mt-2">Administrer</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
