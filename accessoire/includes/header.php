<?php
// /var/www/piece_auto/includes/header.php

// 1. DÉMARRAGE DE SESSION (DOIT ÊTRE LE PREMIER CODE EXÉCUTÉ)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. INCLUSION DES GLOBALS
include_once __DIR__ . '/../config/globals.php';

// 3. VÉRIFICATION DE LA CONNEXION ET REDIRECTION
$is_login_page = basename($_SERVER['PHP_SELF']) == 'login.php';
$app_root = $GLOBALS['app_root'] ?? ''; // Utiliser le chemin racine

if (!$is_login_page && !isset($_SESSION['user_id'])) {
    header('Location: ' . $app_root . '/login.php'); 
    exit;
}

// 4. DÉFINITION DES VARIABLES POUR LE RENDU
$active_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'Guest';
$username = $_SESSION['username'] ?? 'Invité';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PieceAuto ERP - <?= $page_title ?? 'Accueil' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Styles CSS inchangés */
        body { background-color: #f8f9fa; }
        .sidebar { height: 100vh; position: fixed; top: 0; left: 0; width: 250px; padding-top: 56px; background-color: #343a40; color: white; }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 16px; color: #adb5bd; display: block; transition: 0.3s; }
        .sidebar a:hover { color: #ffffff; background-color: #495057; }
        .sidebar a.active { color: #ffffff; background-color: #0d6efd; border-left: 5px solid #ffc107; }
        .content { margin-left: 250px; padding: 20px; }
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

        <?php if (in_array($user_role, ['Admin', 'Stockeur'])): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">RÉFÉRENTIEL</h6>
            <a href="<?= $app_root ?>/modules/gestion_pieces.php" class="<?= $active_page == 'gestion_pieces.php' ? 'active' : '' ?>">
                <i class="fas fa-box"></i> Pièces (Produits)
            </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin', 'Stockeur'])): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">STOCK & ACHATS</h6>
            <a href="<?= $app_root ?>/modules/gestion_achats.php" class="<?= $active_page == 'gestion_achats.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Commandes Achats
            </a>
             <a href="<?= $app_root ?>/modules/gestion_stock.php" class="<?= $active_page == 'gestion_stock.php' ? 'active' : '' ?>">
                <i class="fas fa-truck-loading"></i> Gestion Stock
            </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin', 'Vendeur'])): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">VENTES</h6>
            <a href="<?= $app_root ?>/modules/creation_vente.php" class="<?= $active_page == 'creation_vente.php' ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i> Nouvelle Vente
            </a>
            <a href="<?= $app_root ?>/modules/liste_ventes.php" class="<?= $active_page == 'liste_ventes.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i> Historique Ventes
            </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin', 'Analyse'])): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">STRATÉGIE</h6>
            <a href="<?= $app_root ?>/modules/reporting_strategique.php" class="<?= $active_page == 'reporting_strategique.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i> Reporting Stratégique
            </a>
        <?php endif; ?>

        <?php if ($user_role == 'Admin'): ?>
            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">ADMINISTRATION</h6>
            <a href="<?= $app_root ?>/modules/gestion_utilisateurs.php" class="<?= $active_page == 'gestion_utilisateurs.php' ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i> Utilisateurs & Rôles
            </a>
        <?php endif; ?>
    </div>
    
    <div class="content">
<?php endif; ?>
