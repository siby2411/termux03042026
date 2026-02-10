<?php
// Header SYSCOHADA - Version simplifiée et sécurisée
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Inclure config.php
if (file_exists(dirname(__DIR__) . '/config.php')) {
    require_once dirname(__DIR__) . '/config.php';
} else {
    die("ERREUR: config.php introuvable");
}

// Variables de session
// session_start();
$username = $_SESSION['username'] ?? 'Admin';
$user_role = $_SESSION['user_role'] ?? 'admin';

// Module courant avec valeur par défaut sécurisée
$current_module = 'dashboard';
if (isset($_GET['module'])) {
    $module_param = $_GET['module'];
    if (is_array($module_param)) {
        $current_module = is_array($module_param) && !empty($module_param[0]) ? htmlspecialchars($module_param[0]) : 'dashboard';
    } else {
        $current_module = htmlspecialchars($module_param);
    }
}

// Nettoyer le nom du module
$current_module = preg_replace('/[^a-z_]/', '', strtolower($current_module));

// Sous-module
$current_submodule = isset($_GET['submodule']) ? htmlspecialchars($_GET['submodule']) : '';

// Titre de la page
$page_title = "Tableau de bord";
$module_icon = "tachometer-alt"; // icône par défaut

if (defined('SYSCOHADA_MODULES')) {
    $modules = unserialize(SYSCOHADA_MODULES);
    if (isset($modules[$current_module][0])) {
        $page_title = $modules[$current_module][0];
    }
    if (isset($modules[$current_module][1])) {
        $module_icon = str_replace('fas fa-', '', $modules[$current_module][1]);
    }
}

// Nom du site avec valeur par défaut
$site_name = defined('SITE_NAME') ? SITE_NAME : 'SYSCOHADA';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_name . ' - ' . $page_title; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #2c3e50;
        }
        .sidebar {
            background-color: #34495e;
            min-height: calc(100vh - 56px);
            color: white;
        }
        .main-content {
            padding: 20px;
            min-height: calc(100vh - 56px);
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-dark navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="?module=dashboard">
                <i class="fas fa-calculator"></i> <?php echo $site_name; ?>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?>
                </span>
                <a href="logout.php" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3">
                <h5 class="mb-4">Menu</h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'dashboard' ? 'active' : ''; ?>" 
                           href="?module=dashboard">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'journal' || $current_module == 'journaux' ? 'active' : ''; ?>" 
                           href="?module=journal">
                            <i class="fas fa-book"></i> Journal
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'grand_livre' ? 'active' : ''; ?>" 
                           href="?module=grand_livre">
                            <i class="fas fa-file-invoice"></i> Grand Livre
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'balance' ? 'active' : ''; ?>" 
                           href="?module=balance">
                            <i class="fas fa-balance-scale"></i> Balance
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'comptes' ? 'active' : ''; ?>" 
                           href="?module=comptes">
                            <i class="fas fa-chart-line"></i> Plan Comptable
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'sig' ? 'active' : ''; ?>" 
                           href="?module=sig">
                            <i class="fas fa-chart-bar"></i> SIG
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'ratios' ? 'active' : ''; ?>" 
                           href="?module=ratios">
                            <i class="fas fa-percentage"></i> Ratios
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'budget' ? 'active' : ''; ?>" 
                           href="?module=budget">
                            <i class="fas fa-money-check-alt"></i> Budget
                        </a>
                    </li>
                    

                      <a class="nav-link <?php echo $current_module == 'tiers' ? 'active' : ''; ?>"
   href="?module=tiers">



                            <i class="fas fa-users"></i> Tiers
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'rapports' ? 'active' : ''; ?>" 
                           href="?module=rapports">
                            <i class="fas fa-file-contract"></i> Rapports
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'etats' ? 'active' : ''; ?>" 
                           href="?module=etats">
                            <i class="fas fa-chart-line"></i> États Financiers
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo $current_module == 'parametres' ? 'active' : ''; ?>" 
                    href="?module=parametres">
                            <i class="fas fa-cogs"></i> Paramètres
                        </a>
                    </li>
                </ul>
                
                <div class="mt-5 pt-5 border-top border-secondary">
                    <small class="text-muted">
                        <?php echo defined('COMPANY_NAME') ? COMPANY_NAME : 'SYSCOHADA'; ?><br>
                        &copy; <?php echo date('Y'); ?> SYSCOHADA
                    </small>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-md-10 main-content">
                <h3 class="mb-4">
                    <i class="fas fa-<?php echo $module_icon; ?>"></i>
                    <?php echo $page_title; ?>
                </h3>
                <div id="content">
