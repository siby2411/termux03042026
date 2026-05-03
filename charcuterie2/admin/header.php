<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>OMEGA Charcuterie - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar {
            width: 260px;
            background: #2c3e50;
            color: white;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: #e74c3c;
            padding-left: 30px;
        }
        .sidebar a.active {
            background: #e74c3c;
            border-left: 4px solid #f1c40f;
        }
        .sidebar i {
            width: 25px;
            margin-right: 10px;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
            background: #f5f5f5;
            min-height: 100vh;
        }
        .top-bar {
            background: #e74c3c;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-area {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #34495e;
        }
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar span:not(.icon-only) { display: none; }
            .content { margin-left: 70px; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-area">
            <i class="fas fa-utensils fa-2x"></i>
            <div style="font-weight: bold; margin-top: 5px;">OMEGA</div>
            <small>Charcuterie</small>
        </div>
        <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> <span>Tableau de bord</span>
        </a>
        <a href="pos.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pos.php' ? 'active' : '' ?>">
            <i class="fas fa-cash-register"></i> <span>Point de Vente</span>
        </a>
        <a href="produits.php" class="<?= basename($_SERVER['PHP_SELF']) == 'produits.php' ? 'active' : '' ?>">
            <i class="fas fa-boxes"></i> <span>Produits</span>
        </a>
        <a href="categories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> <span>Catégories</span>
        </a>
        <a href="ventes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ventes.php' ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i> <span>Ventes</span>
        </a>
        <a href="clients.php" class="<?= basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> <span>Clients</span>
        </a>
        <a href="fournisseurs.php" class="<?= basename($_SERVER['PHP_SELF']) == 'fournisseurs.php' ? 'active' : '' ?>">
            <i class="fas fa-truck"></i> <span>Fournisseurs</span>
        </a>
        <a href="stock.php" class="<?= basename($_SERVER['PHP_SELF']) == 'stock.php' ? 'active' : '' ?>">
            <i class="fas fa-warehouse"></i> <span>Stock</span>
        </a>
        <a href="rapports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rapports.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> <span>Rapports</span>
        </a>
        <hr style="margin: 10px 0; border-color: #34495e;">
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span>
        </a>
    </div>
    
    <div class="content">
        <div class="top-bar">
            <div>
                <i class="fas fa-user-circle"></i> <?= $_SESSION['admin_nom'] ?? 'Administrateur' ?>
                <small>(<?= $_SESSION['admin_role'] ?? 'admin' ?>)</small>
            </div>
            <div>
                <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y H:i') ?>
            </div>
        </div>
