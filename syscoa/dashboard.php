<?php
// dashboard.php - Dashboard principal intégré
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

// Définition des modules disponibles
$modules = [
    'accueil' => [
        'title' => 'Tableau de bord',
        'icon' => 'fas fa-home',
        'category' => 'principal',
        'file' => 'modules/dashboard_accueil.php'
    ],
    'comptabilite' => [
        'title' => 'Écritures comptables',
        'icon' => 'fas fa-book',
        'category' => 'comptabilite',
        'file' => 'modules/ecritures_comptables.php'
    ],
    'rapprochement' => [
        'title' => 'Rapprochement bancaire',
        'icon' => 'fas fa-university',
        'category' => 'banque',
        'file' => 'modules/rapprochement_bancaire.php'
    ],
    'soldes' => [
        'title' => 'Soldes intermédiaires',
        'icon' => 'fas fa-chart-line',
        'category' => 'analyse',
        'file' => 'modules/soldes_gestion.php'
    ],
    'articles' => [
        'title' => 'Gestion des articles',
        'icon' => 'fas fa-boxes',
        'category' => 'stock',
        'file' => 'modules/gestion_articles.php'
    ],
    'cloture' => [
        'title' => 'Travaux de clôture',
        'icon' => 'fas fa-calendar-times',
        'category' => 'cloture',
        'file' => 'modules/travaux_cloture.php'
    ],
    'bilans' => [
        'title' => 'Bilans et états',
        'icon' => 'fas fa-balance-scale',
        'category' => 'rapports',
        'file' => 'modules/bilans_etats.php'
    ],
    'grand_livre' => [
        'title' => 'Grand livre',
        'icon' => 'fas fa-book-open',
        'category' => 'comptabilite',
        'file' => 'modules/grand_livre.php'
    ],
    'journaux' => [
        'title' => 'Journaux comptables',
        'icon' => 'fas fa-file-invoice',
        'category' => 'comptabilite',
        'file' => 'modules/journaux_comptables.php'
    ],
    'inventaire' => [
        'title' => 'Inventaire stock',
        'icon' => 'fas fa-clipboard-check',
        'category' => 'stock',
        'file' => 'modules/inventaire_stock.php'
    ],
    'ratios' => [
        'title' => 'Ratios financiers',
        'icon' => 'fas fa-percentage',
        'category' => 'analyse',
        'file' => 'modules/ratios_financiers.php'
    ],
    'releves' => [
        'title' => 'Relevés bancaires',
        'icon' => 'fas fa-file-alt',
        'category' => 'banque',
        'file' => 'modules/releves_bancaires.php'
    ],
    'amortissements' => [
        'title' => 'Amortissements',
        'icon' => 'fas fa-calculator',
        'category' => 'cloture',
        'file' => 'modules/amortissements.php'
    ],
    'provisions' => [
        'title' => 'Provisions',
        'icon' => 'fas fa-shield-alt',
        'category' => 'cloture',
        'file' => 'modules/provisions.php'
    ],
    'rapports' => [
        'title' => 'Rapports financiers',
        'icon' => 'fas fa-chart-bar',
        'category' => 'rapports',
        'file' => 'modules/rapports_financiers.php'
    ],
    'etats' => [
        'title' => 'États légaux',
        'icon' => 'fas fa-gavel',
        'category' => 'rapports',
        'file' => 'modules/etats_legaux.php'
    ]
];

// Module par défaut
$current_module = isset($_GET['module']) ? $_GET['module'] : 'accueil';
if (!isset($modules[$current_module])) {
    $current_module = 'accueil';
}

// Récupérer les statistiques globales
$stats = getGlobalStats($pdo);
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modules[$current_module]['title']; ?> - SYSCO OHADA</title>
    
    <!-- Framework CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- CSS Personnalisé -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/modules.css">
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="brand">
                <div class="logo">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="brand-text">
                    <h1>SYSCO OHADA</h1>
                    <span class="version">v2.0 Professional</span>
                </div>
            </div>
        </div>
        
        <div class="nav-center">
            <div class="module-title">
                <i class="<?php echo $modules[$current_module]['icon']; ?>"></i>
                <h2><?php echo $modules[$current_module]['title']; ?></h2>
            </div>
        </div>
        
        <div class="nav-right">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <button class="btn-quick" title="Recherche rapide">
                    <i class="fas fa-search"></i>
                </button>
                <button class="btn-quick" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                <button class="btn-quick" title="Aide">
                    <i class="fas fa-question-circle"></i>
                </button>
            </div>
            
            <!-- User Menu -->
            <div class="user-menu">
                <div class="user-avatar">
                    <img src="assets/images/avatar.png" alt="<?php echo $_SESSION['username']; ?>">
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username']; ?></span>
                    <span class="user-role"><?php echo $_SESSION['user_role']; ?></span>
                </div>
                <button class="user-dropdown">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            
            <!-- Exercice Selector -->
            <div class="exercice-selector">
                <select id="exerciceSelect" class="form-select">
                    <option value="1">Exercice 2023</option>
                    <option value="2" selected>Exercice 2024</option>
                </select>
            </div>
        </div>
    </nav>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="current-exercice">
                    <div class="exercice-info">
                        <span class="exercice-label">Exercice en cours</span>
                        <h3 class="exercice-name"><?php echo $_SESSION['exercice_nom']; ?></h3>
                        <span class="exercice-dates">01/01/2024 - 31/12/2024</span>
                    </div>
                    <div class="exercice-status">
                        <span class="status-badge status-open">OUVERT</span>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <!-- Section Principale -->
                <div class="nav-section">
                    <h3 class="section-title">PRINCIPAL</h3>
                    <ul class="nav-list">
                        <li class="nav-item <?php echo $current_module == 'accueil' ? 'active' : ''; ?>">
                            <a href="?module=accueil" class="nav-link">
                                <i class="fas fa-home"></i>
                                <span>Tableau de bord</span>
                                <span class="nav-badge"><?php echo $stats['total_ecritures']; ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Section Comptabilité -->
                <div class="nav-section">
                    <h3 class="section-title">COMPTABILITÉ</h3>
                    <ul class="nav-list">
                        <li class="nav-item <?php echo $current_module == 'comptabilite' ? 'active' : ''; ?>">
                            <a href="?module=comptabilite" class="nav-link">
                                <i class="fas fa-book"></i>
                                <span>Écritures comptables</span>
                                <span class="nav-badge"><?php echo $stats['ecritures_mois']; ?></span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_module == 'grand_livre' ? 'active' : ''; ?>">
                            <a href="?module=grand_livre" class="nav-link">
                                <i class="fas fa-book-open"></i>
                                <span>Grand livre</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_module == 'journaux' ? 'active' : ''; ?>">
                            <a href="?module=journaux" class="nav-link">
                                <i class="fas fa-file-invoice"></i>
                                <span>Journaux comptables</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Section Banque -->
                <div class="nav-section">
                    <h3 class="section-title">BANQUE</h3>
                    <ul class="nav-list">
                        <li class="nav-item <?php echo $current_module == 'rapprochement' ? 'active' : ''; ?>">
                            <a href="?module=rapprochement" class="nav-link">
                                <i class="fas fa-university"></i>
                                <span>Rapprochement bancaire</span>
                                <?php if ($stats['rapprochements_pending'] > 0): ?>
                                <span class="nav-badge badge-warning"><?php echo $stats['rapprochements_pending']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_module == 'releves' ? 'active' : ''; ?>">
                            <a href="?module=releves" class="nav-link">
                                <i class="fas fa-file-alt"></i>
                                <span>Relevés bancaires</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Section Analyse Financière -->
                <div class="nav-section">
                    <h3 class="section-title">ANALYSE FINANCIÈRE</h3>
                    <ul class="nav-list">
                        <li class="nav-item <?php echo $current_module == 'soldes' ? 'active' : ''; ?>">
                            <a href="?module=soldes" class="nav-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Soldes intermédiaires</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_module == 'bilans' ? 'active' : ''; ?>">
                            <a href="?module=bilans" class="nav-link">
                                <i class="fas fa-balance-scale"></i>
                                <span>Bilans et états</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_module == 'ratios' ? 'active' : ''; ?>">
                            <a href="?module=ratios" class="nav-link">
                                <i class="fas fa-percentage"></i>
                                <span>Ratios financiers</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Section Stocks -->
                <div class="nav-section">
                    <h3 class="section-title">GESTION DES STOCKS</h3>
                    <ul class="nav-list">
                        <li class="nav-item <?php echo $current_module == 'articles' ? 'active' : ''; ?>">
                            <a href="?module=articles" class="nav-link">
                                <i class="fas fa-boxes"></i>
                                <span>Gestion des articles</span>
                                <span class="nav-badge"><?php echo $stats['total_articles']; ?></span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_module == 'inventaire' ? 'active' : ''; ?>">
                            <a href="?module=inventaire" class="nav-link">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Inventaire stock</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Section Clôture -->
                <div class="nav-section">
                    <h3 class="section-title">CLÔTURE EXERCICE</h3>
                    <ul class="nav-list">
                        <li class="nav-item <?php echo $current_module == 'cloture' ? 'active' : ''; ?>">
                            <a href="?module=cloture" class="nav-link">
                                <i class="fas fa-calendar-times"></i>
                                <span>Travaux de clôture</span>
                                <?php if ($stats['etapes_pending'] > 0): ?>
                                <span class="nav-badge badge-danger"><?php echo $stats['etapes_pending']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_module == 'amortissements' ? 'active' : ''; ?>">
                            <a href="?module=amortissements" class="nav-link">
                                <i class="fas fa-calculator"></i>
                                <span>Amortissements</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_module == 'provisions' ? 'active' : ''; ?>">
                            <a href="?module=provisions" class="nav-link">
                                <i class="fas fa-shield-alt"></i>
                                <span>Provisions</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Section Rapports -->
                <div class="nav-section">
                    <h3 class="section-title">RAPPORTS</h3>
                    <ul class="nav-list">
                        <li class="nav-item <?php echo $current_module == 'rapports' ? 'active' : ''; ?>">
                            <a href="?module=rapports" class="nav-link">
                                <i class="fas fa-chart-bar"></i>
                                <span>Rapports financiers</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_module == 'etats' ? 'active' : ''; ?>">
                            <a href="?module=etats" class="nav-link">
                                <i class="fas fa-gavel"></i>
                                <span>États légaux</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Section Administration -->
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <div class="nav-section">
                    <h3 class="section-title">ADMINISTRATION</h3>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="administration.php" class="nav-link">
                                <i class="fas fa-cogs"></i>
                                <span>Paramètres système</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="utilisateurs.php" class="nav-link">
                                <i class="fas fa-users-cog"></i>
                                <span>Gestion utilisateurs</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="backup.php" class="nav-link">
                                <i class="fas fa-database"></i>
                                <span>Sauvegarde</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </nav>
            
            <div class="sidebar-footer">
                <div class="system-status">
                    <div class="status-indicator active"></div>
                    <span>Système en ligne</span>
                </div>
                <button class="btn-logout" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </button>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <ol>
                    <li><a href="?module=accueil"><i class="fas fa-home"></i> Accueil</a></li>
                    <li class="separator">/</li>
                    <li class="current"><?php echo $modules[$current_module]['title']; ?></li>
                </ol>
                
                <div class="breadcrumb-actions">
                    <button class="btn-action" title="Actualiser" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn-action" title="Aide" onclick="openHelp()">
                        <i class="fas fa-question-circle"></i>
                    </button>
                    <button class="btn-action" title="Exporter">
                        <i class="fas fa-download"></i>
                    </button>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <button class="btn-action btn-danger" title="Options admin">
                        <i class="fas fa-cogs"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </nav>
            
            <!-- Module Content -->
            <div class="module-container">
                <?php 
                // Charger le module demandé
                $module_file = $modules[$current_module]['file'];
                if (file_exists($module_file)) {
                    include $module_file;
                } else {
                    echo '<div class="alert alert-danger">Module non disponible</div>';
                }
                ?>
            </div>
            
            <!-- Quick Stats Footer -->
            <div class="quick-stats">
                <div class="stat-item">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo number_format($stats['total_ecritures'], 0, ',', ' '); ?></span>
                        <span class="stat-label">Écritures</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-university"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo number_format($stats['solde_banque'], 0, ',', ' ') . ' F'; ?></span>
                        <span class="stat-label">Solde bancaire</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-boxes"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo number_format($stats['total_articles'], 0, ',', ' '); ?></span>
                        <span class="stat-label">Articles stock</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-calendar-alt"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $stats['jours_restants']; ?></span>
                        <span class="stat-label">Jours avant clôture</span>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modals -->
    <div id="quickSearchModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-search"></i> Recherche rapide</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" class="search-input" placeholder="Rechercher dans le système...">
                <div class="search-results"></div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/modules.js"></script>
    <script>
    // Initialisation des DataTables
    $(document).ready(function() {
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            pageLength: 25,
            responsive: true
        });
    });
    
    // Gestion du sidebar
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('mainContent').classList.toggle('expanded');
    });
    
    // Fonction de déconnexion
    function logout() {
        Swal.fire({
            title: 'Déconnexion',
            text: 'Êtes-vous sûr de vouloir vous déconnecter ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui, déconnecter',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    }
    
    // Aide contextuelle
    function openHelp() {
        Swal.fire({
            title: 'Aide',
            html: `
                <h4>Module : ${document.title}</h4>
                <p>Pour toute assistance, contactez le support technique.</p>
                <p><strong>Hotline :</strong> +237 XXX XXX XXX</p>
                <p><strong>Email :</strong> support@sysco-ohada.com</p>
            `,
            icon: 'info',
            confirmButtonText: 'Fermer'
        });
    }
    </script>
</body>
</html>
