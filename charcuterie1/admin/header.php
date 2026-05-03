<?php
// Vérifier la session
if (session_status() === PHP_SESSION_NONE) session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA Charcuterie - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background: #e74c3c;
            padding-left: 30px;
        }
        .sidebar a.active {
            background: #e74c3c;
        }
        .content {
            padding: 20px;
        }
        .navbar-custom {
            background: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="text-center py-4">
                    <i class="fas fa-utensils fa-3x"></i>
                    <h5 class="mt-2">OMEGA Charcuterie</h5>
                    <small>Administration</small>
                </div>
                <hr class="bg-light">
                <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
                </a>
                <a href="produits.php" class="<?= basename($_SERVER['PHP_SELF']) == 'produits.php' ? 'active' : '' ?>">
                    <i class="fas fa-boxes me-2"></i> Produits
                </a>
                <a href="categories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                    <i class="fas fa-tags me-2"></i> Catégories
                </a>
                <a href="ventes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ventes.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart me-2"></i> Ventes
                </a>
                <a href="clients.php" class="<?= basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : '' ?>">
                    <i class="fas fa-users me-2"></i> Clients
                </a>
                <a href="fournisseurs.php" class="<?= basename($_SERVER['PHP_SELF']) == 'fournisseurs.php' ? 'active' : '' ?>">
                    <i class="fas fa-truck me-2"></i> Fournisseurs
                </a>
                <a href="stock.php" class="<?= basename($_SERVER['PHP_SELF']) == 'stock.php' ? 'active' : '' ?>">
                    <i class="fas fa-warehouse me-2"></i> Stock
                </a>
                <a href="rapports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rapports.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line me-2"></i> Rapports
                </a>
                <hr class="bg-light">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                </a>
            </div>
            
            <!-- Main content -->
            <div class="col-md-10 p-0">
                <nav class="navbar navbar-custom text-white p-3">
                    <div>
                        <i class="fas fa-user-circle me-2"></i>
                        <?= $_SESSION['admin_nom'] ?? 'Administrateur' ?>
                        <small class="ms-2">(<?= $_SESSION['admin_role'] ?? 'admin' ?>)</small>
                    </div>
                </nav>
                <div class="content">
