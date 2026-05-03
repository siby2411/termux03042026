<?php
// dashboard_main.php
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

// Récupérer les statistiques générales
$stats = [
    'total_ecritures' => $pdo->query("SELECT COUNT(*) FROM ecritures WHERE id_exercice = " . $_SESSION['id_exercice'])->fetchColumn(),
    'total_tiers' => $pdo->query("SELECT COUNT(*) FROM tiers WHERE actif = 1")->fetchColumn(),
    'total_articles' => $pdo->query("SELECT COUNT(*) FROM articles_stock")->fetchColumn(),
    'solde_banque' => $pdo->query("SELECT SUM(debit - credit) FROM ecritures WHERE compte_num LIKE '52%'")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Principal - SYSCO OHADA</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>SYSCO OHADA</h2>
                <p>V.2.0</p>
            </div>
            
            <div class="user-info">
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?></div>
                <div>
                    <h4><?php echo $_SESSION['username']; ?></h4>
                    <p><?php echo $_SESSION['user_role']; ?></p>
                </div>
            </div>
            
            <nav class="main-menu">
                <ul>
                    <li><a href="dashboard_main.php" class="active"><i class="fas fa-home"></i> Tableau de bord</a></li>
                    
                    <!-- Module Comptabilité -->
                    <li class="menu-category">COMPTABILITÉ</li>
                    <li><a href="ecritures.php"><i class="fas fa-book"></i> Écritures comptables</a></li>
                    <li><a href="journaux.php"><i class="fas fa-file-invoice"></i> Journaux comptables</a></li>
                    <li><a href="grand_livre.php"><i class="fas fa-book-open"></i> Grand livre</a></li>
                    
                    <!-- Module Rapprochement Bancaire -->
                    <li class="menu-category">BANQUE</li>
                    <li><a href="rapprochement_bancaire.php"><i class="fas fa-university"></i> Rapprochement bancaire</a></li>
                    <li><a href="releves_bancaires.php"><i class="fas fa-file-alt"></i> Relevés bancaires</a></li>
                    
                    <!-- Module Soldes Intermédiaires -->
                    <li class="menu-category">ANALYSE FINANCIÈRE</li>
                    <li><a href="soldes_gestion.php"><i class="fas fa-chart-line"></i> Soldes intermédiaires</a></li>
                    <li><a href="bilans.php"><i class="fas fa-balance-scale"></i> Bilans</a></li>
                    <li><a href="ratios_financiers.php"><i class="fas fa-percentage"></i> Ratios financiers</a></li>
                    
                    <!-- Module Gestion des Stocks -->
                    <li class="menu-category">STOCKS</li>
                    <li><a href="gestion_articles.php"><i class="fas fa-boxes"></i> Gestion des articles</a></li>
                    <li><a href="mouvements_stock.php"><i class="fas fa-exchange-alt"></i> Mouvements de stock</a></li>
                    <li><a href="inventaire.php"><i class="fas fa-clipboard-check"></i> Inventaire</a></li>
                    
                    <!-- Module Clôture -->
                    <li class="menu-category">CLÔTURE</li>
                    <li><a href="travaux_cloture.php"><i class="fas fa-calendar-times"></i> Travaux de clôture</a></li>
                    <li><a href="amortissements.php"><i class="fas fa-calculator"></i> Amortissements</a></li>
                    <li><a href="provisions.php"><i class="fas fa-shield-alt"></i> Provisions</a></li>
                    
                    <!-- Module Rapports -->
                    <li class="menu-category">RAPPORTS</li>
                    <li><a href="rapports_financiers.php"><i class="fas fa-chart-bar"></i> Rapports financiers</a></li>
                    <li><a href="etats_legaux.php"><i class="fas fa-gavel"></i> États légaux</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>Tableau de bord</h1>
                    <p>Exercice en cours : <?php echo $_SESSION['exercice_nom']; ?></p>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" onclick="openQuickMenu()">
                        <i class="fas fa-bolt"></i> Actions rapides
                    </button>
                </div>
            </header>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_ecritures'], 0, ',', ' '); ?></h3>
                        <p>Écritures comptables</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_tiers'], 0, ',', ' '); ?></h3>
                        <p>Tiers actifs</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #FF9800;">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_articles'], 0, ',', ' '); ?></h3>
                        <p>Articles en stock</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #9C27B0;">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['solde_banque'], 0, ',', ' ') . ' FCFA'; ?></h3>
                        <p>Solde bancaire</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Modules -->
            <div class="modules-grid">
                <!-- Module Soldes Intermédiaires -->
                <div class="module-card">
                    <div class="module-header">
                        <h3><i class="fas fa-chart-line"></i> Soldes Intermédiaires de Gestion</h3>
                        <a href="soldes_gestion.php" class="btn-sm">Voir détails</a>
                    </div>
                    <div class="module-content">
                        <?php include 'modules/sig_preview.php'; ?>
                    </div>
                </div>
                
                <!-- Module Rapprochement Bancaire -->
                <div class="module-card">
                    <div class="module-header">
                        <h3><i class="fas fa-university"></i> Rapprochement Bancaire</h3>
                        <a href="rapprochement_bancaire.php" class="btn-sm">Voir détails</a>
                    </div>
                    <div class="module-content">
                        <?php include 'modules/rapprochement_preview.php'; ?>
                    </div>
                </div>
                
                <!-- Module Gestion Articles -->
                <div class="module-card">
                    <div class="module-header">
                        <h3><i class="fas fa-boxes"></i> Gestion des Articles</h3>
                        <a href="gestion_articles.php" class="btn-sm">Voir détails</a>
                    </div>
                    <div class="module-content">
                        <?php include 'modules/articles_preview.php'; ?>
                    </div>
                </div>
                
                <!-- Module Travaux Clôture -->
                <div class="module-card">
                    <div class="module-header">
                        <h3><i class="fas fa-calendar-times"></i> Travaux de Clôture</h3>
                        <a href="travaux_cloture.php" class="btn-sm">Voir détails</a>
                    </div>
                    <div class="module-content">
                        <?php include 'modules/cloture_preview.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function openQuickMenu() {
        // Menu rapide pour les actions fréquentes
        alert('Menu actions rapides - À implémenter');
    }
    </script>
</body>
</html>
