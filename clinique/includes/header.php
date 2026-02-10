<?php
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'auth.php';
redirectIfNotLoggedIn();

// Menu par rôle - Chemins ABSOLUS depuis la racine
$menus = [
    'Medecin' => [
        ['icon' => '📊', 'label' => 'Tableau de Bord', 'link' => '/clinique/modules/dashboard.php'],
        ['icon' => '👥', 'label' => 'Mes Patients', 'link' => '/clinique/modules/patients/list.php'],
        ['icon' => '📅', 'label' => 'Mes Rendez-vous', 'link' => '/clinique/modules/rendezvous/list.php'],
        ['icon' => '🩺', 'label' => 'Consultations', 'link' => '/clinique/modules/consultations/list.php'],
        ['icon' => '🔬', 'label' => 'Analyses', 'link' => '/clinique/modules/analyses/list.php']
    ],
    'Infirmier' => [
        ['icon' => '📊', 'label' => 'Tableau de Bord', 'link' => '/clinique/modules/dashboard.php'],
        ['icon' => '👥', 'label' => 'Patients', 'link' => '/clinique/modules/patients/list.php'],
        ['icon' => '🩺', 'label' => 'Soins', 'link' => '/clinique/modules/consultations/soins.php'],
        ['icon' => '💊', 'label' => 'Médicaments', 'link' => '/clinique/modules/stock/medicaments.php']
    ],
    'Admin' => [
        ['icon' => '📊', 'label' => 'Tableau de Bord', 'link' => '/clinique/modules/dashboard.php'],
        ['icon' => '👥', 'label' => 'Patients', 'link' => '/clinique/modules/patients/list.php'],
        ['icon' => '👨‍⚕️', 'label' => 'Personnel', 'link' => '/clinique/modules/personnel/list.php'],
        ['icon' => '📅', 'label' => 'Rendez-vous', 'link' => '/clinique/modules/rendezvous/list.php'],
        ['icon' => '🩺', 'label' => 'Consultations', 'link' => '/clinique/modules/consultations/list.php'],
        ['icon' => '💰', 'label' => 'Finances', 'link' => '/clinique/modules/finances/list.php'],
        ['icon' => '🔬', 'label' => 'Analyses', 'link' => '/clinique/modules/analyses/list.php'],
        ['icon' => '📋', 'label' => 'Journal Audit', 'link' => '/clinique/modules/audit/list.php'],
        ['icon' => '⚙️', 'label' => 'Paramètres', 'link' => '/clinique/modules/admin/settings.php']
    ],
    'Secretaire' => [
        ['icon' => '📊', 'label' => 'Tableau de Bord', 'link' => '/clinique/modules/dashboard.php'],
        ['icon' => '👥', 'label' => 'Patients', 'link' => '/clinique/modules/patients/list.php'],
        ['icon' => '📅', 'label' => 'Rendez-vous', 'link' => '/clinique/modules/rendezvous/list.php'],
        ['icon' => '💰', 'label' => 'Facturation', 'link' => '/clinique/modules/finances/list.php']
    ],
    'Comptable' => [
        ['icon' => '📊', 'label' => 'Tableau de Bord', 'link' => '/clinique/modules/dashboard.php'],
        ['icon' => '💰', 'label' => 'Finances', 'link' => '/clinique/modules/finances/list.php'],
        ['icon' => '📈', 'label' => 'Rapports', 'link' => '/clinique/modules/finances/rapports.php']
    ]
];

$currentRole = getUserRole();
$userMenu = $menus[$currentRole] ?? $menus['Medecin'];

// Déterminer le chemin de base
$base_url = '/clinique';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinique Sénégal - Gestion Médicale</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    
    <style>
        .sidebar {
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            min-height: 100vh;
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        .nav-item {
            border-radius: 8px;
            margin: 3px 15px;
            transition: all 0.3s ease;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }
        .nav-link {
            color: white !important;
            padding: 12px 15px;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .nav-icon {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            margin-right: 12px;
        }
        .user-info {
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 8px 15px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .sidebar-header {
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header p-3">
                <div class="text-center">
                    <h4 class="text-white mb-1">
                        <i class="bi bi-heart-pulse me-2"></i>
                        Clinique Sénégal
                    </h4>
                    <small class="text-white text-opacity-75">Gestion Médicale</small>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-white text-opacity-60">
                        <i class="bi bi-person me-1"></i>
                        <?php echo getUserName(); ?>
                    </small>
                    <span class="badge bg-light text-dark ms-2"><?php echo $currentRole; ?></span>
                </div>
            </div>
            
            <div class="sidebar-nav p-2 mt-2">
                <?php foreach ($userMenu as $item): ?>
                <a href="<?php echo $item['link']; ?>" class="nav-item nav-link">
                    <span class="nav-icon"><?php echo $item['icon']; ?></span>
                    <?php echo $item['label']; ?>
                </a>
                <?php endforeach; ?>
                
                <!-- Séparateur -->
                <div class="my-3 mx-3 border-top border-white border-opacity-25"></div>
                
                <!-- Lien de déconnexion dans la sidebar -->
                <a href="<?php echo $base_url; ?>/logout.php" class="nav-item nav-link text-warning">
                    <span class="nav-icon">🚪</span>
                    Déconnexion
                </a>
            </div>
            
            <!-- Footer de la sidebar -->
            <div class="sidebar-footer p-3 position-absolute bottom-0 start-0 end-0 border-top border-white border-opacity-25">
                <small class="text-white text-opacity-50 d-block text-center">
                    © <?php echo date('Y'); ?> Clinique Sénégal
                </small>
                <small class="text-white text-opacity-50 d-block text-center">
                    Version 1.0
                </small>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content flex-grow-1">
            <!-- Top Header -->
            <header class="navbar navbar-light bg-white border-bottom shadow-sm sticky-top">
                <div class="container-fluid">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-outline-secondary me-3 d-lg-none" type="button" id="sidebarToggle">
                                <i class="bi bi-list"></i>
                            </button>
                            <h5 class="mb-0 text-primary" id="pageTitle">
                                <?php 
                                $current_page = basename($_SERVER['PHP_SELF']);
                                $page_titles = [
                                    'list.php' => 'Liste',
                                    'add.php' => 'Ajouter',
                                    'edit.php' => 'Modifier',
                                    'delete.php' => 'Supprimer',
                                    'view.php' => 'Détails',
                                    'dossier.php' => 'Dossier Médical',
                                    'dashboard.php' => 'Tableau de Bord',
                                    'action.php' => 'Action'
                                ];
                                echo $page_titles[$current_page] ?? 'Gestion';
                                ?>
                            </h5>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <!-- Recherche globale -->
                            <div class="input-group me-3 d-none d-md-flex" style="width: 300px;">
                                <input type="text" class="form-control" placeholder="Rechercher patient, personnel..." id="globalSearch">
                                <button class="btn btn-outline-primary" type="button" id="searchButton">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            
                            <!-- Indicateur de statut -->
                            <div class="user-info me-3">
                                <span class="text-white">
                                    <i class="bi bi-circle-fill text-success me-1"></i>
                                    Connecté
                                </span>
                            </div>
                            
                            <!-- Menu utilisateur -->
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?php echo getUserName(); ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header"><?php echo $currentRole; ?></h6></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Mon Profil</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="<?php echo $base_url; ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="container-fluid p-4">
