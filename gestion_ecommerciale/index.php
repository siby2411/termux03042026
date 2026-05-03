<?php
// Fichier : index.php - Portail Public
session_start();

// Si l'utilisateur est déjà connecté, on le redirige directement vers le dashboard
if (isset($_SESSION['id_vendeur'])) {
    header("Location: dashboard.php");
    exit();
}

// Inclusion du header (s'il contient la structure HTML de base)
include 'header.php';
?>

<div class="container py-5">
    <div class="row align-items-center g-5">
        <div class="col-lg-6 text-center text-lg-start">
            <h1 class="display-4 fw-bold lh-1 mb-3 text-primary">Système de Gestion Commerciale</h1>
            <p class="col-lg-10 fs-4 text-muted">
                Optimisez la gestion de vos stocks, suivez vos ventes en temps réel et simplifiez votre facturation avec notre solution intégrée. 
            </p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                <a href="login.php" class="btn btn-primary btn-lg px-4 me-md-2 shadow">
                    <i class="fas fa-sign-in-alt"></i> Espace Collaborateur
                </a>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-4 border rounded-3 bg-white shadow-sm text-center">
                        <h2 class="fw-bold text-success">📦</h2>
                        <p class="mb-0 fw-bold">Stocks</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 border rounded-3 bg-white shadow-sm text-center">
                        <h2 class="fw-bold text-info">📄</h2>
                        <p class="mb-0 fw-bold">Facturation</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 border rounded-3 bg-white shadow-sm text-center">
                        <h2 class="fw-bold text-warning">👥</h2>
                        <p class="mb-0 fw-bold">Clients</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 border rounded-3 bg-white shadow-sm text-center">
                        <h2 class="fw-bold text-danger">📊</h2>
                        <p class="mb-0 fw-bold">Rapports</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <div class="row text-center g-4">
        <div class="col-md-4">
            <div class="p-3">
                <i class="fas fa-bolt fa-3x text-primary mb-3"></i>
                <h3>Rapide</h3>
                <p>Une interface optimisée pour des saisies de ventes ultra-rapides.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3">
                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                <h3>Sécurisé</h3>
                <p>Accès restreints par vendeur avec traçabilité complète des opérations.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3">
                <i class="fas fa-mobile-alt fa-3x text-info mb-3"></i>
                <h3>Mobile</h3>
                <p>Consultez vos rapports de vente depuis votre smartphone n'importe où.</p>
            </div>
        </div>
    </div>
</div>

<?php 
include 'footer.php'; 
?>
