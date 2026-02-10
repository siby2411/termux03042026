<?php
// /var/www/piece_auto/public/index.php
include '../includes/header.php'; 
$page_title = "Tableau de Bord";
?>

<h1 class="mb-4 text-center fw-bolder" style="color: var(--primary-color);">
    <i class="fas fa-tachometer-alt"></i> Espace de Gestion des Pièces Automobile
</h1>
<p class="text-center text-muted mb-5">Vue d'ensemble et accès rapide aux modules essentiels.</p>

<div class="row g-4">
    <div class="col-lg-4 col-md-6">
        <a href="../modules/gestion_pieces.php" class="card p-4 h-100 text-dark module-tile">
            <div class="text-center">
                <span style="font-size: 3rem; color: #3498db;"><i class="fas fa-box-open"></i></span>
                <h3 class="mt-3 fw-bold">Catalogue Pièces</h3>
                <p class="text-muted small">Ajouter, modifier ou consulter le catalogue complet des pièces (références, compatibilité, prix).</p>
                <button class="btn btn-sm btn-info mt-2">Gérer les Pièces <i class="fas fa-arrow-right ms-1"></i></button>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="../modules/gestion_stock.php" class="card p-4 h-100 text-dark module-tile">
            <div class="text-center">
                <span style="font-size: 3rem; color: #e67e22;"><i class="fas fa-cubes"></i></span>
                <h3 class="mt-3 fw-bold">Stock Actuel</h3>
                <p class="text-muted small">Suivi des quantités disponibles, inventaire, emplacements et alertes de stock faible.</p>
                <button class="btn btn-sm btn-warning mt-2">Voir le Stock <i class="fas fa-arrow-right ms-1"></i></button>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="../modules/gestion_ventes.php" class="card p-4 h-100 text-dark module-tile">
            <div class="text-center">
                <span style="font-size: 3rem; color: #27ae60;"><i class="fas fa-shopping-cart"></i></span>
                <h3 class="mt-3 fw-bold">Ventes Rapides</h3>
                <p class="text-muted small">Enregistrement des transactions clients, génération de devis et de factures.</p>
                <button class="btn btn-sm btn-success mt-2">Nouvelle Vente <i class="fas fa-arrow-right ms-1"></i></button>
            </div>
        </a>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <a href="../modules/gestion_achats.php" class="card p-4 h-100 text-dark module-tile">
            <div class="text-center">
                <span style="font-size: 3rem; color: #8e44ad;"><i class="fas fa-truck"></i></span>
                <h3 class="mt-3 fw-bold">Achats / Commandes</h3>
                <p class="text-muted small">Gestion des commandes passées, réception de la marchandise et suivi des fournisseurs.</p>
                <button class="btn btn-sm" style="background-color: #8e44ad; color: white;">Gérer les Achats <i class="fas fa-arrow-right ms-1"></i></button>
            </div>
        </a>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <a href="../modules/gestion_clients.php" class="card p-4 h-100 text-dark module-tile">
            <div class="text-center">
                <span style="font-size: 3rem; color: #34495e;"><i class="fas fa-users"></i></span>
                <h3 class="mt-3 fw-bold">Clients</h3>
                <p class="text-muted small">Base de données clients, historique des achats et informations de contact.</p>
                <button class="btn btn-sm btn-secondary mt-2">Voir les Clients <i class="fas fa-arrow-right ms-1"></i></button>
            </div>
        </a>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <a href="../modules/analyse_ventes.php" class="card p-4 h-100 text-dark module-tile">
            <div class="text-center">
                <span style="font-size: 3rem; color: #e74c3c;"><i class="fas fa-chart-line"></i></span>
                <h3 class="mt-3 fw-bold">Rapports Clés</h3>
                <p class="text-muted small">Analyse des ventes, marges bénéficiaires, pièces les plus vendues et performance.</p>
                <button class="btn btn-sm btn-auto mt-2">Consulter les Rapports <i class="fas fa-arrow-right ms-1"></i></button>
            </div>
        </a>
    </div>
    
</div>


<a href="../modules/analyse_ventes.php" class="card p-4 h-100 text-dark module-tile">



<?php 
include '../includes/footer.php'; 
?>
