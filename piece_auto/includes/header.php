<?php
// /var/www/piece_auto/includes/header.php

// 1. DÉMARRAGE DE SESSION (Doit être la première chose si aucune sortie n'a été envoyée)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. INCLUSION DES GLOBALS
// Utilisez __DIR__ pour garantir le chemin absolu depuis le fichier 'header.php'
include_once __DIR__ . '/../config/globals.php'; 

// 3. VÉRIFICATION DE LA CONNEXION ET REDIRECTION
$is_login_page = basename($_SERVER['PHP_SELF']) == 'login.php';
$app_root = $GLOBALS['app_root'] ?? '';

if (!$is_login_page && !isset($_SESSION['user_id'])) {
    header('Location: ' . $app_root . '/login.php'); 
    exit;
}

// 4. DÉFINITION DES VARIABLES POUR LE RENDU
$active_page = basename($_SERVER['PHP_SELF']);
// Si la session n'est pas initialisée (cas login.php), on utilise 'Guest'
$user_role = $_SESSION['user_role'] ?? 'Guest';
$username = $_SESSION['username'] ?? 'Invité';

// Pages implémentées pour une mise en évidence visuelle plus forte (active-link)
// CORRECTION : Ajout de gestion_commandes_vente.php
$implemented_pages = [
    'gestion_categories.php', 
    'rapports_financiers.php',
    'gestion_stock.php',             // NOUVEAU
    'gestion_achats.php',            // NOUVEAU
    'creation_commande_achat.php',   // NOUVEAU
    'gestion_commandes_vente.php',   // AJOUTÉ MAINTENANT
];

// Fonction pour déterminer la classe
function get_link_class($active_page, $target_page, $implemented_pages) {
    // Si la page est la page active ET que la page est dans la liste des pages implémentées
    if ($active_page == $target_page && in_array($target_page, $implemented_pages)) {
        return 'active-link';
    }
    // Si la page est la page active (pour les liens non implémentés de l'ancien header)
    if ($active_page == $target_page) {
        return 'active';
    }
    // Sinon, retourne la classe par défaut ''
    return ''; 
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PieceAuto ERP - <?= $page_title ?? 'Accueil' ?></title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { 
            height: 100vh; position: fixed; top: 0; left: 0; width: 250px; 
            padding-top: 56px; background-color: #343a40; color: white; z-index: 1000;
            overflow-y: auto;
        }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 16px; color: #adb5bd; display: block; transition: 0.3s; }
        .sidebar a:hover { color: #ffffff; background-color: #495057; }
        /* Style pour l'ancien système (tous les liens actifs non 'active-link') */
        .sidebar a.active { color: #ffffff; background-color: #495057; border-left: 5px solid #ffc107; } 
        /* Nouveau style pour mettre en évidence les modules fonctionnels */
        .sidebar a.active-link { color: #ffffff; background-color: #0d6efd; border-left: 5px solid #ffc107; }

        .content { margin-left: 250px; padding: 20px; padding-top: 76px; } /* Ajusté pour la barre fixe */
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>

<?php if (!$is_login_page): ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="fas fa-car-side"></i> PieceAuto ERP
            </span>
            <span class="navbar-text ms-auto me-3 text-white">
                Connecté en tant que: **<?= htmlspecialchars($username) ?>** (Rôle: **<?= htmlspecialchars($user_role) ?>**)
            </span>
            <a href="<?= $app_root ?>/logout.php" class="btn btn-outline-danger">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </nav>

    <div class="sidebar">
        <a href="<?= $app_root ?>/index.php" class="<?= $active_page == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Tableau de Bord
        </a>

        <?php if (in_array($user_role, ['Admin', 'Stockeur', 'Vendeur'])): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">RÉFÉRENTIEL & MAÎTRISE</h6>
            
            <a href="<?= $app_root ?>/modules/gestion_pieces.php" class="<?= $active_page == 'gestion_pieces.php' ? 'active' : '' ?>">
                <i class="fas fa-box"></i> Pièces (Produits)
            </a>
            <a href="<?= $app_root ?>/modules/gestion_categories.php" class="<?= get_link_class($active_page, 'gestion_categories.php', $implemented_pages) ?>">
                <i class="fas fa-boxes"></i> Catégories (AJOUTÉ)
            </a>
            <a href="<?= $app_root ?>/modules/gestion_clients.php" class="<?= $active_page == 'gestion_clients.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Gestion Clients
            </a>
            <a href="<?= $app_root ?>/modules/gestion_fournisseurs.php" class="<?= $active_page == 'gestion_fournisseurs.php' ? 'active' : '' ?>">
                <i class="fas fa-truck-moving"></i> Gestion Fournisseurs
            </a>
           
 


<a href="<?= $app_root ?>/modules/gestion_rappels.php" class="<?= $active_page == 'gestion_rappels.php' ? 'active' : '' ?>">
    <i class="fas fa-exclamation-triangle"></i> Gestion Rappels Auto
</a>



            <a href="<?= $app_root ?>/modules/tracabilite_vin.php" class="<?= $active_page == 'tracabilite_vin.php' ? 'active' : '' ?>">
                <i class="fas fa-id-card"></i> Traçabilité VIN
            </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin', 'Stockeur', 'Acheteur'])): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">STOCK & ACHATS</h6>
           

<a href="<?php echo $app_root ?>/modules/gestion_pieces.php" class="<?= get_link_class($active_page, 'gestion_pieces.php', $implemented_pages) ?>">
    <i class="fas fa-tools"></i> Gestion des Pièces
</a>




<a href="<?= $app_root ?>/modules/suggestion_reappro.php" class="<?= get_link_class($active_page, 'suggestion_reappro.php', $implemented_pages) ?>">
    <i class="fas fa-dolly-flatbed"></i> Suggestions Réappro.
</a>



<a href="<?= $app_root ?>/modules/alertes_stock.php" class="<?= $active_page == 'alertes_stock.php' ? 'active' : '' ?>">
    <i class="fas fa-exclamation-circle"></i> Alertes Stock Min.
</a>


            <a href="<?= $app_root ?>/modules/gestion_achats.php" class="<?= get_link_class($active_page, 'gestion_achats.php', $implemented_pages) ?>">
                <i class="fas fa-shopping-cart"></i> Commandes Achats
            </a>
            <a href="<?= $app_root ?>/modules/creation_commande_achat.php" class="<?= get_link_class($active_page, 'creation_commande_achat.php', $implemented_pages) ?>">
                <i class="fas fa-file-invoice"></i> Créer Commande Achat
            </a>


<a href="<?= $app_root ?>/modules/reception_achats.php" class="<?= $active_page == 'reception_achats.php' ? 'active' : '' ?>">
    <i class="fas fa-dolly-flatbed"></i> Enregistrer Réception
</a>
            <a href="<?= $app_root ?>/modules/gestion_budgets_achat.php" class="<?= $active_page == 'gestion_budgets_achat.php' ? 'active' : '' ?>">
                <i class="fas fa-money-bill-alt"></i> Budgets Achats
            </a>
        <?php endif; ?>
        
        <?php if (in_array($user_role, ['Admin', 'Vendeur', 'Logistique'])): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">VENTES & LOGISTIQUE</h6>
            <a href="<?= $app_root ?>/modules/creation_vente.php" class="<?= $active_page == 'creation_vente.php' ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i> Nouvelle Vente
            </a>
            <a href="<?= $app_root ?>/modules/gestion_ventes.php" class="<?= $active_page == 'gestion_ventes.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i> Historique Ventes
            </a>
            
            <a href="<?= $app_root ?>/modules/gestion_commandes_vente.php" class="<?= get_link_class($active_page, 'gestion_commandes_vente.php', $implemented_pages) ?>">
                <i class="fas fa-receipt"></i> **Facturation & Commandes**
            </a>

            <a href="<?= $app_root ?>/modules/gestion_retours.php" class="<?= $active_page == 'gestion_retours.php' ? 'active' : '' ?>">
                <i class="fas fa-undo-alt"></i> Gestion des Retours
            </a>
            <a href="<?= $app_root ?>/modules/gestion_tournees.php" class="<?= $active_page == 'gestion_tournees.php' ? 'active' : '' ?>">
                <i class="fas fa-route"></i> Gestion des Tournées
            </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin', 'Analyse', 'Manager'])): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">ANALYSES & RAPPORTS</h6>
            


<a href="<?= $app_root ?>/modules/historique_mouvements_stock.php" class="<?= $active_page == 'historique_mouvements_stock.php' ? 'active' : '' ?>">
    <i class="fas fa-exchange-alt"></i> Mouvements de Stock Détaillés
</a>


            <a href="<?= $app_root ?>/modules/rapports_financiers.php" class="<?= get_link_class($active_page, 'rapports_financiers.php', $implemented_pages) ?>">
                <i class="fas fa-chart-bar"></i> Rapports Financiers (AJOUTÉ)
            </a>
            

<a href="<?= $app_root ?>/modules/tableau_de_bord.php" class="<?= $active_page == 'tableau_de_bord.php' ? 'active' : '' ?>">
    <i class="fas fa-tachometer-alt"></i> Tableau de Bord
</a>
            <a href="<?= $app_root ?>/modules/reporting_strategique.php" class="<?= $active_page == 'reporting_strategique.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i> Reporting Stratégique
            </a>
            <a href="<?= $app_root ?>/modules/analyse_ventes.php" class="<?= $active_page == 'analyse_ventes.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Analyse des Ventes
            </a>
            <a href="<?= $app_root ?>/modules/analyse_marges.php" class="<?= $active_page == 'analyse_marges.php' ? 'active' : '' ?>">
                <i class="fas fa-percentage"></i> Analyse des Marges
            </a>
            <a href="<?= $app_root ?>/modules/analyse_stock_mouvements.php" class="<?= $active_page == 'analyse_stock_mouvements.php' ? 'active' : '' ?>">
                <i class="fas fa-exchange-alt"></i> Mouvements de Stock
            </a>
            <a href="<?= $app_root ?>/modules/forecasting_stock.php" class="<?= $active_page == 'forecasting_stock.php' ? 'active' : '' ?>">
                <i class="fas fa-magic"></i> Prévisions Stock
            </a>
            <a href="<?= $app_root ?>/modules/analyse_clients.php" class="<?= $active_page == 'analyse_clients.php' ? 'active' : '' ?>">
                <i class="fas fa-user-tie"></i> Analyse Clients
            </a>
            <a href="<?= $app_root ?>/modules/analyse_motorisation.php" class="<?= $active_page == 'analyse_motorisation.php' ? 'active' : '' ?>">
                <i class="fas fa-engine"></i> Analyse Motorisation
            </a>
            <a href="<?= $app_root ?>/modules/analyse_pannes.php" class="<?= $active_page == 'analyse_pannes.php' ? 'active' : '' ?>">
                <i class="fas fa-heartbeat"></i> Analyse Pannes
            </a>
            <a href="<?= $app_root ?>/modules/impact_rappel.php" class="<?= $active_page == 'impact_rappel.php' ? 'active' : '' ?>">
                <i class="fas fa-bullhorn"></i> Impact Rappel
            </a>
        <?php endif; ?>

        <?php if ($user_role == 'Admin'): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">ADMINISTRATION</h6>

            <a href="<?= $app_root ?>/modules/gestion_utilisateurs.php" class="<?= $active_page == 'gestion_utilisateurs.php' ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i> Utilisateurs & Rôles
            </a>
            <?php endif; ?>



<a href="<?= $app_root ?>/modules/gestion_catalogue.php" class="<?= $active_page == 'gestion_catalogue.php' ? 'active' : '' ?>">
    <i class="fas fa-search"></i> Catalogue Public
</a>


<a href="<?= $app_root ?>/modules/documentation.php" class="<?= $active_page == 'documentation.php' ? 'active' : '' ?>">
    <i class="fas fa-book-open"></i> Documentation Modules
</a>


<a href="imprimer_facture.php?id=<?= $cmd['id_commande_vente'] ?>" target="_blank" class="btn btn-primary">
    <i class="fas fa-print"></i> Imprimer
</a>



<a href="<?= $app_root ?>/modules/creer_commande_vente.php" class="<?= get_link_class($active_page, 'creer_commande_vente.php', $implemented_pages) ?>">
    <i class="fas fa-shopping-cart"></i> Nouvelle Vente
</a>




    </div>

    <div class="content">
<?php endif; ?>
<script src="https:/.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
