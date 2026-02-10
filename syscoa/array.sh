# Créer un nouveau header.php corrigé
sudo tee /var/www/syscoa/includes/header.php << 'EOF'
<?php
// Header SYSCOHADA - Version corrigée
require_once dirname(__DIR__) . '/config.php';
check_auth();

$current_module = $_GET['module'] ?? DEFAULT_MODULE;
$current_submodule = $_GET['submodule'] ?? DEFAULT_SUBMODULE;
$username = $_SESSION['username'] ?? 'Utilisateur';
$user_role = $_SESSION['user_role'] ?? 'user';

// Récupérer tous les modules
$all_modules = unserialize(SYSCOHADA_MODULES);

// Filtrer les modules accessibles
$accessible_modules = [];
foreach ($all_modules as $key => $module) {
    if (has_module_access($key, $user_role)) {
        $accessible_modules[$key] = $module;
    }
}

// Vérifier si le module courant est accessible
if (!isset($accessible_modules[$current_module])) {
    $current_module = DEFAULT_MODULE;
}

$current_module_info = $accessible_modules[$current_module] ?? $all_modules[DEFAULT_MODULE];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME . ' - ' . htmlspecialchars($current_module_info[0]); ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles personnalisés -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding-top: 60px;
        }
        
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: white;
            height: calc(100vh - 60px);
            position: fixed;
            left: 0;
            top: 60px;
            overflow-y: auto;
            transition: all 0.3s;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }
        
        .sidebar-menu .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--secondary-color);
        }
        
        .sidebar-menu .nav-link.active {
            color: white;
            background: rgba(52, 152, 219, 0.2);
            border-left-color: var(--secondary-color);
        }
        
        .sidebar-menu .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: calc(100vh - 60px);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation principale -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-balance-scale me-2"></i>
                <strong><?php echo SITE_NAME; ?></strong>
                <small class="ms-2 opacity-75">v<?php echo SYSCOHADA_VERSION; ?></small>
            </a>
            
            <div class="navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo htmlspecialchars($username); ?>
                            <span class="badge bg-light text-dark ms-1"><?php echo htmlspecialchars($user_role); ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Mon compte</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-question-circle me-2"></i>Aide</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-menu">
            <?php foreach ($accessible_modules as $key => $module): ?>
                <a class="nav-link <?php echo ($current_module === $key) ? 'active' : ''; ?>" 
                   href="index.php?module=<?php echo $key; ?>">
                    <i class="<?php echo $module[1]; ?>"></i>
                    <span><?php echo $module[0]; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="position-absolute bottom-0 start-0 w-100 p-3 text-center border-top border-secondary">
            <small class="text-muted">
                <?php echo COMPANY_NAME; ?><br>
                &copy; <?php echo date('Y'); ?> SYSCOHADA
            </small>
        </div>
    </div>
    
    <!-- Contenu principal -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php?module=dashboard">Tableau de bord</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $current_module_info[0]; ?></li>
                </ol>
            </nav>
            
            <!-- En-tête du module -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="<?php echo $current_module_info[1]; ?> me-2"></i>
                        <?php echo $current_module_info[0]; ?>
                    </h4>
                </div>
EOF

# Créer un footer.php corrigé
sudo tee /var/www/syscoa/includes/footer.php << 'EOF'
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personnalisés -->
    <script>
        // Initialisation des tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Gestion des messages d'alerte
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>
EOF
