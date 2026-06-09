<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title ?? 'OMEGA PIECES' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; min-height: 100vh; background-color: #f4f7f6; overflow-x: hidden; }
        #sidebar { width: 260px; background: #2c3e50; color: white; flex-shrink: 0; position: sticky; top: 0; height: 100vh; overflow-y: auto; }
        #sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 10px 20px; font-size: 0.9rem; transition: 0.2s; }
        #sidebar .nav-link:hover { background: #34495e; color: white; }
        #sidebar .nav-link i { width: 20px; text-align: center; margin-right: 10px; }
        #sidebar .section-title { font-size: 0.65rem; text-transform: uppercase; padding: 15px 20px 5px; color: #95a5a6; letter-spacing: 1.5px; font-weight: bold; }
        #content { flex-grow: 1; padding: 25px; background: #f8f9fa; min-width: 0; }
        .nav-link.active { background: #3498db !important; color: white !important; }
        /* Custom scrollbar pour la sidebar */
        #sidebar::-webkit-scrollbar { width: 5px; }
        #sidebar::-webkit-scrollbar-thumb { background: #455a64; }
    </style>
</head>
<body>

<div id="sidebar">
    <div class="p-4 border-bottom border-secondary text-center">
        <h4 class="fw-bold mb-0 text-white">OMEGA PIECES</h4>
        <small class="text-info">Expert System v1.0</small>
    </div>
    
    <nav class="nav flex-column mb-5">
        <a class="nav-link" href="/modules/tableau_de_bord.php"><i class="fas fa-home"></i> Dashboard</a>
        
        <div class="section-title">Commerce & Ventes</div>
        <a class="nav-link" href="/modules/creer_commande_vente.php"><i class="fas fa-cart-plus"></i> Nouvelle Vente</a>
        <a class="nav-link" href="/modules/gestion_commandes_vente.php"><i class="fas fa-list-ul"></i> Historique Ventes</a>
        <a class="nav-link" href="/modules/gestion_clients.php"><i class="fas fa-user-friends"></i> Clients</a>
        <a class="nav-link" href="/modules/gestion_retours.php"><i class="fas fa-undo"></i> Retours Clients</a>

        <div class="section-title">Stock & Logistique</div>
        <a class="nav-link" href="/modules/gestion_catalogue.php"><i class="fas fa-book-open"></i> Catalogue Pièces</a>
        <a class="nav-link" href="/modules/gestion_stock.php"><i class="fas fa-boxes"></i> État des Stocks</a>
        <a class="nav-link" href="/modules/historique_mouvements_stock.php"><i class="fas fa-exchange-alt"></i> Flux & Mouvements</a>
        <a class="nav-link" href="/modules/tracabilite_vin.php"><i class="fas fa-search"></i> Recherche par VIN</a>
        <a class="nav-link" href="/modules/alertes_stock.php"><i class="fas fa-exclamation-triangle"></i> Alertes Rupture</a>

        <div class="section-title">Achats & Fournisseurs</div>
        <a class="nav-link" href="/modules/gestion_fournisseurs.php"><i class="fas fa-truck"></i> Fournisseurs</a>
        <a class="nav-link" href="/modules/gestion_commandes_achat.php"><i class="fas fa-file-invoice-dollar"></i> Bons de Commande</a>
        <a class="nav-link" href="/modules/reception_achats.php"><i class="fas fa-download"></i> Réception Stock</a>

        <div class="section-title">Analyses & Stratégie</div>
        <a class="nav-link" href="/modules/reporting_strategique.php"><i class="fas fa-chart-line"></i> Rapports de Ventes</a>
        <a class="nav-link" href="/modules/forecasting_stock.php"><i class="fas fa-brain"></i> Prévisions IA</a>
        <a class="nav-link" href="/modules/tableau_bord_rentabilite.php"><i class="fas fa-dollar-sign"></i> Rentabilité Brute</a>

        <div class="section-title">Administration</div>
        <a class="nav-link" href="/modules/gestion_utilisateurs.php"><i class="fas fa-user-shield"></i> Utilisateurs</a>
        <a class="nav-link" href="/modules/documentation.php"><i class="fas fa-info-circle"></i> Aide & Docs</a>
        
        <div class="mt-4 p-3 border-top border-secondary">
             <a href="/logout.php" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-power-off"></i> Quitter</a>
        </div>
    </nav>
</div>

<div id="content">
    <div class="container-fluid">
