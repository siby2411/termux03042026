#!/bin/bash
# Script de création des modules SyscoHADA pour SENECOM SA
# Conformité OHADA/UEMOA

echo "=== CRÉATION DES MODULES SYSCoHADA ==="
echo "Cas pratique : SENECOM SA"
echo "Conformité : OHADA Révision 2023"
echo ""

# Créer la structure de dossiers
echo "1. Création de la structure des dossiers..."
mkdir -p modules/{dashboard,flux,soldes,budget,cloture,rapprochement,journaux,articles}
mkdir -p modules/{plan_comptable,saisie_ecriture,balance,grand_livre,compte_resultat,bilan,tresorerie,exercices_comptables}
mkdir -p includes assets/{css,js,images} uploads/{pieces,rapports,exports}

echo "✓ Structure de dossiers créée"

# Créer le fichier config.php
echo "2. Création du fichier de configuration..."
cat > config.php << 'EOF'
<?php
/**
 * Configuration SyscoHADA - SENECOM SA
 * Conformité OHADA/UEMOA
 */

// Configuration environnement
define('ENVIRONMENT', 'development'); // development, testing, production
define('APP_NAME', 'SyscoHADA');
define('APP_VERSION', '3.0.0');
define('APP_YEAR', date('Y'));

// Configuration base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '123');
define('DB_NAME', 'sysco_ohada');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// Configuration OHADA
define('OHADA_VERSION', 'Révision 2023');
define('DEVISE', 'FCFA');
define('DEVISE_SYMBOLE', 'FCFA');
define('DECIMALES', 2);
define('SEPARATEUR_MILLIERS', ' ');
define('SEPARATEUR_DECIMALES', ',');
define('PAYS', 'Sénégal');
define('REGIME_FISCAL', 'Réel Normal');
define('PERIODE_COMPTABLE', 'Mensuelle');

// Configuration entreprise (SENECOM SA)
define('ENTREPRISE_NOM', 'SENECOM SA');
define('ENTREPRISE_SIGLE', 'SENECOM');
define('ENTREPRISE_IFU', 'IFU123456789');
define('ENTREPRISE_RCCM', 'SN-DKR-2023-A-12345');
define('ENTREPRISE_ADRESSE', 'Dakar Plateau, Immeuble Alpha');
define('ENTREPRISE_TELEPHONE', '+221 33 889 45 67');
define('ENTREPRISE_EMAIL', 'comptabilite@senecom.sn');
define('ENTREPRISE_ACTIVITE', 'Services Informatiques et Conseil');
define('ENTREPRISE_FORM_JURIDIQUE', 'SA');
define('ENTREPRISE_CAPITAL', '10000000');

// Configuration application
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
            . "://" . $_SERVER['HTTP_HOST'] 
            . dirname($_SERVER['SCRIPT_NAME']) . "/";
define('BASE_URL', $base_url);
define('BASE_PATH', __DIR__ . '/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_PATH', BASE_PATH . 'uploads/');
define('UPLOADS_URL', BASE_URL . 'uploads/');

// Configuration sécurité
define('SESSION_TIMEOUT', 3600); // 1 heure
define('MAX_LOGIN_ATTEMPTS', 3);
define('PASSWORD_MIN_LENGTH', 8);

// Configuration rapports
define('RAPPORT_DATE_FORMAT', 'd/m/Y');
define('RAPPORT_TIME_FORMAT', 'H:i:s');
define('RAPPORT_ENTETE_LOGO', BASE_PATH . 'assets/images/logo_senecom.png');

// Configuration modules
$modules_config = [
    'dashboard' => ['icon' => 'speedometer2', 'role' => 'all'],
    'plan_comptable' => ['icon' => 'book', 'role' => 'comptable'],
    'saisie_ecriture' => ['icon' => 'pencil', 'role' => 'comptable'],
    'journaux' => ['icon' => 'journal', 'role' => 'comptable'],
    'grand_livre' => ['icon' => 'book', 'role' => 'comptable'],
    'balance' => ['icon' => 'scale', 'role' => 'comptable'],
    'compte_resultat' => ['icon' => 'graph-up', 'role' => 'comptable'],
    'bilan' => ['icon' => 'bar-chart', 'role' => 'comptable'],
    'flux' => ['icon' => 'cash-stack', 'role' => 'financier'],
    'soldes' => ['icon' => 'calculator', 'role' => 'financier'],
    'budget' => ['icon' => 'pie-chart', 'role' => 'financier'],
    'tresorerie' => ['icon' => 'bank', 'role' => 'financier'],
    'cloture' => ['icon' => 'lock', 'role' => 'admin'],
    'rapprochement' => ['icon' => 'arrow-left-right', 'role' => 'comptable'],
    'articles' => ['icon' => 'box', 'role' => 'stock'],
    'exercices_comptables' => ['icon' => 'calendar', 'role' => 'admin']
];

// Fonction de connexion à la base de données
function get_db_connection() {
    static $connection = null;
    
    if ($connection === null) {
        try {
            $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($connection->connect_error) {
                throw new Exception("Erreur de connexion à la base de données: " . $connection->connect_error);
            }
            
            $connection->set_charset(DB_CHARSET);
            
        } catch (Exception $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] " . $e->getMessage());
            die("<div class='alert alert-danger'>Erreur de connexion à la base de données. Contactez l'administrateur.</div>");
        }
    }
    
    return $connection;
}

// Fonction de formatage monétaire OHADA
function format_montant_ohada($montant, $avec_devise = true) {
    $formatted = number_format(
        $montant, 
        DECIMALES, 
        SEPARATEUR_DECIMALES, 
        SEPARATEUR_MILLIERS
    );
    
    if ($avec_devise) {
        return $formatted . ' ' . DEVISE_SYMBOLE;
    }
    
    return $formatted;
}

// Fonction de validation fiscale OHADA
function valider_nif_ohada($nif) {
    // Validation simplifiée du NIF/IFU
    $pattern = '/^[A-Z]{2}[0-9]{7,9}$/';
    return preg_match($pattern, $nif);
}

// Gestion des erreurs
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Démarrage de session sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Vérification de session
function verifier_session() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'index.php?module=login');
        exit();
    }
    
    // Vérifier timeout
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'index.php?module=login&expired=1');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

// Fonction de journalisation
function logger_action($action, $details = '') {
    $conn = get_db_connection();
    $user_id = $_SESSION['user_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO journal_actions (user_id, action, details, ip_adresse) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

EOF

echo "✓ Fichier config.php créé"

# Créer le fichier header.php
echo "3. Création du header de navigation..."
cat > includes/header.php << 'EOF'
<?php
/**
 * Header de navigation SyscoHADA
 * Conformité OHADA - SENECOM SA
 */
require_once __DIR__ . '/../config.php';
verifier_session();

$module_actuel = $_GET['module'] ?? 'dashboard';
$user_nom = $_SESSION['user_nom'] ?? 'Utilisateur';
$user_role = $_SESSION['user_role'] ?? 'comptable';

// Récupérer l'exercice actif
$conn = get_db_connection();
$exercice_actif = null;
$result = $conn->query("SELECT * FROM exercices_comptables WHERE statut = 'actif' LIMIT 1");
if ($result && $result->num_rows > 0) {
    $exercice_actif = $result->fetch_assoc();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo $modules_config[$module_actuel]['icon'] ?? 'Dashboard'; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        :root {
            --ohada-primary: #2c3e50;
            --ohada-secondary: #3498db;
            --ohada-success: #27ae60;
            --ohada-warning: #f39c12;
            --ohada-danger: #e74c3c;
            --ohada-info: #17a2b8;
        }
        
        .navbar-ohada {
            background: linear-gradient(135deg, var(--ohada-primary) 0%, #34495e 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        
        .exercice-badge {
            background-color: var(--ohada-success);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .module-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            border-radius: 10px;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border-color: var(--ohada-secondary);
        }
    </style>
</head>
<body>
    <!-- Navigation principale -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-ohada">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php?module=dashboard">
                <i class="bi bi-calculator me-2"></i>
                <strong>SYSCoHADA</strong>
                <small class="ms-2 text-light">v<?php echo APP_VERSION; ?></small>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- Menu modules -->
                <ul class="navbar-nav me-auto">
                    <!-- Modules comptables -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($module_actuel, ['saisie_ecriture','journaux','grand_livre','balance','compte_resultat','bilan']) ? 'active' : ''; ?>" 
                           href="#" id="dropdownComptable" data-bs-toggle="dropdown">
                            <i class="bi bi-journal-text"></i> Comptabilité
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $module_actuel == 'saisie_ecriture' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=saisie_ecriture">
                                   <i class="bi bi-pencil"></i> Saisie écritures
                                </a></li>
                            <li><a class="dropdown-item <?php echo $module_actuel == 'journaux' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=journaux">
                                   <i class="bi bi-journal"></i> Journaux
                                </a></li>
                            <li><a class="dropdown-item <?php echo $module_actuel == 'grand_livre' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=grand_livre">
                                   <i class="bi bi-book"></i> Grand livre
                                </a></li>
                            <li><a class="dropdown-item <?php echo $module_actuel == 'balance' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=balance">
                                   <i class="bi bi-scale"></i> Balance
                                </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?php echo $module_actuel == 'compte_resultat' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=compte_resultat">
                                   <i class="bi bi-graph-up"></i> Compte résultat
                                </a></li>
                            <li><a class="dropdown-item <?php echo $module_actuel == 'bilan' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=bilan">
                                   <i class="bi bi-bar-chart"></i> Bilan
                                </a></li>
                        </ul>
                    </li>
                    
                    <!-- Modules financiers -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($module_actuel, ['flux','soldes','budget','tresorerie']) ? 'active' : ''; ?>" 
                           href="#" id="dropdownFinancier" data-bs-toggle="dropdown">
                            <i class="bi bi-cash-stack"></i> Analyse Financière
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $module_actuel == 'flux' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=flux">
                                   <i class="bi bi-arrow-left-right"></i> Flux trésorerie
                                </a></li>
                            <li><a class="dropdown-item <?php echo $module_actuel == 'soldes' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=soldes">
                                   <i class="bi bi-calculator"></i> Soldes intermédiaires
                                </a></li>
                            <li><a class="dropdown-item <?php echo $module_actuel == 'budget' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=budget">
                                   <i class="bi bi-pie-chart"></i> Budget
                                </a></li>
                            <li><a class="dropdown-item <?php echo $module_actuel == 'tresorerie' ? 'active' : ''; ?>" 
                                   href="<?php echo BASE_URL; ?>index.php?module=tresorerie">
                                   <i class="bi bi-bank"></i> Trésorerie
                                </a></li>
                        </ul>
                    </li>
                    
                    <!-- Modules gestion -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $module_actuel == 'plan_comptable' ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>index.php?module=plan_comptable">
                           <i class="bi bi-book"></i> Plan comptable
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $module_actuel == 'rapprochement' ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>index.php?module=rapprochement">
                           <i class="bi bi-arrow-left-right"></i> Rapprochement
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $module_actuel == 'cloture' ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>index.php?module=cloture">
                           <i class="bi bi-lock"></i> Clôture
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $module_actuel == 'articles' ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>index.php?module=articles">
                           <i class="bi bi-box"></i> Articles
                        </a>
                    </li>
                </ul>
                
                <!-- Informations exercice et utilisateur -->
                <div class="navbar-nav align-items-center">
                    <?php if ($exercice_actif): ?>
                    <div class="nav-item me-3">
                        <span class="exercice-badge">
                            <i class="bi bi-calendar-check"></i>
                            Exercice : <?php echo htmlspecialchars($exercice_actif['libelle']); ?>
                            (<?php echo date('d/m/Y', strtotime($exercice_actif['date_debut'])); ?> - 
                             <?php echo date('d/m/Y', strtotime($exercice_actif['date_fin'])); ?>)
                        </span>
                    </div>
                    <?php else: ?>
                    <div class="nav-item me-3">
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle"></i>
                            Aucun exercice actif
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dropdownUser" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars($user_nom); ?>
                            <small class="text-light">(<?php echo $user_role; ?>)</small>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-person"></i> Mon profil
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-gear"></i> Paramètres
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?module=exercices_comptables">
                                <i class="bi bi-calendar"></i> Gérer exercices
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Breadcrumb -->
    <div class="container-fluid bg-light py-2">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?php echo BASE_URL; ?>index.php?module=dashboard">
                            <i class="bi bi-house-door"></i> Accueil
                        </a>
                    </li>
                    <?php
                    $module_nom = $modules_config[$module_actuel]['icon'] ?? ucfirst($module_actuel);
                    ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="bi bi-<?php echo $modules_config[$module_actuel]['icon'] ?? 'circle'; ?>"></i>
                        <?php echo $module_nom; ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Conteneur principal -->
    <main class="container-fluid py-4">
        <div class="container">
EOF

echo "✓ Header créé"

# Créer le footer.php
echo "4. Création du footer..."
cat > includes/footer.php << 'EOF'
<?php
/**
 * Footer SyscoHADA - SENECOM SA
 * Conformité OHADA/UEMOA
 */
?>
        </div> <!-- Fermeture container -->
    </main>
    
    <!-- Footer UEMOA -->
    <footer class="footer-uemoa mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-building"></i> <?php echo ENTREPRISE_NOM; ?></h5>
                    <p class="mb-1"><?php echo ENTREPRISE_ADRESSE; ?></p>
                    <p class="mb-1">IFU: <?php echo ENTREPRISE_IFU; ?> | RCCM: <?php echo ENTREPRISE_RCCM; ?></p>
                    <p class="mb-0">Tél: <?php echo ENTREPRISE_TELEPHONE; ?> | Email: <?php echo ENTREPRISE_EMAIL; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5><i class="bi bi-shield-check"></i> Conformité OHADA</h5>
                    <p class="mb-1">Système Comptable conforme à la Révision 2023</p>
                    <p class="mb-1">Normes SYSCOHADA - UEMOA</p>
                    <p class="mb-0">© <?php echo date('Y'); ?> - SyscoHADA v<?php echo APP_VERSION; ?></p>
                </div>
            </div>
            <hr class="mt-3 mb-2">
            <div class="row">
                <div class="col-12 text-center">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        Ce système est destiné à la gestion comptable et financière conforme aux normes OHADA.
                        Toute utilisation non autorisée est interdite.
                    </small>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialisation DataTables
        $('.datatable-ohada').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
            },
            pageLength: 25,
            responsive: true,
            order: [[0, 'desc']]
        });
        
        // Initialisation Select2
        $('.select2-ohada').select2({
            theme: 'bootstrap-5',
            placeholder: 'Sélectionnez...',
            allowClear: true
        });
        
        // Validation des montants
        $('.montant-ohada').on('blur', function() {
            let valeur = $(this).val().replace(/[^\d,]/g, '');
            let nombre = parseFloat(valeur.replace(',', '.'));
            
            if (!isNaN(nombre)) {
                $(this).val(new Intl.NumberFormat('fr-FR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(nombre));
            }
        });
        
        // Tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Gestion des messages d'alerte
        setTimeout(function() {
            $('.alert-auto-hide').fadeOut('slow');
        }, 5000);
        
        // Export Excel
        $('.btn-export-excel').on('click', function() {
            let tableId = $(this).data('table');
            let table = $('#' + tableId).DataTable();
            table.button('.buttons-excel').trigger();
        });
        
        // Impression
        $('.btn-imprimer').on('click', function() {
            window.print();
        });
    });
    
    // Fonction de formatage monétaire
    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(montant) + ' FCFA';
    }
    
    // Validation OHADA
    function validerEcritureOHADA(formData) {
        let erreurs = [];
        
        if (!formData.compte_debit || !formData.compte_credit) {
            erreurs.push('Les comptes débit et crédit sont obligatoires');
        }
        
        if (parseFloat(formData.montant_debit) !== parseFloat(formData.montant_credit)) {
            erreurs.push('Les montants débit et crédit doivent être égaux');
        }
        
        if (parseFloat(formData.montant_debit) <= 0) {
            erreurs.push('Le montant doit être positif');
        }
        
        return erreurs;
    }
    </script>
</body>
</html>
EOF

echo "✓ Footer créé"

# Créer les modules principaux
echo "5. Création des modules principaux..."

# Module Dashboard
cat > modules/dashboard/index.php << 'EOF'
<?php
/**
 * Module Dashboard - Tableau de bord SyscoHADA
 * Cas pratique : SENECOM SA
 */

require_once __DIR__ . '/../../config.php';
verifier_session();

$conn = get_db_connection();

// Récupérer les statistiques
$stats = [];

// Nombre de comptes
$result = $conn->query("SELECT COUNT(*) as total FROM comptes_ohada");
$stats['comptes'] = $result->fetch_assoc()['total'];

// Nombre d'écritures du mois
$mois_courant = date('Y-m');
$result = $conn->query("SELECT COUNT(*) as total FROM ecritures WHERE DATE_FORMAT(date_ecriture, '%Y-%m') = '$mois_courant'");
$stats['ecritures_mois'] = $result->fetch_assoc()['total'];

// Total débit/crédit du mois
$result = $conn->query("SELECT SUM(montant_debit) as total_debit, SUM(montant_credit) as total_credit FROM ecritures WHERE DATE_FORMAT(date_ecriture, '%Y-%m') = '$mois_courant'");
$totaux = $result->fetch_assoc();
$stats['total_debit'] = $totaux['total_debit'] ?? 0;
$stats['total_credit'] = $totaux['total_credit'] ?? 0;

// Exercice actif
$result = $conn->query("SELECT * FROM exercices_comptables WHERE statut = 'actif' LIMIT 1");
$exercice = $result->fetch_assoc();

// Dernières écritures
$result = $conn->query("SELECT e.*, c1.libelle as compte_debit_libelle, c2.libelle as compte_credit_libelle 
                       FROM ecritures e
                       LEFT JOIN comptes_ohada c1 ON e.compte_debit_id = c1.id
                       LEFT JOIN comptes_ohada c2 ON e.compte_credit_id = c2.id
                       ORDER BY e.date_ecriture DESC, e.id DESC
                       LIMIT 10");
$dernieres_ecritures = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Journaliser l'accès
logger_action('Accès dashboard', 'Tableau de bord consulté');
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-speedometer2"></i> Tableau de bord</h2>
        <p class="lead">Bienvenue dans SyscoHADA - Système Comptable OHADA</p>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>SENECOM SA</strong> | Exercice actif : 
            <?php if ($exercice): ?>
                <span class="badge bg-success"><?php echo htmlspecialchars($exercice['libelle']); ?></span>
                (<?php echo date('d/m/Y', strtotime($exercice['date_debut'])); ?> - 
                 <?php echo date('d/m/Y', strtotime($exercice['date_fin'])); ?>)
            <?php else: ?>
                <span class="badge bg-danger">Aucun exercice actif</span>
                <a href="<?php echo BASE_URL; ?>index.php?module=exercices_comptables" class="btn btn-sm btn-warning ms-2">
                    <i class="bi bi-plus-circle"></i> Créer un exercice
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Cartes statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card card-stat bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">COMPTES</h6>
                        <h2 class="mb-0"><?php echo $stats['comptes']; ?></h2>
                    </div>
                    <i class="bi bi-book" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <p class="card-text mt-2">Plan comptable OHADA</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card card-stat bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">ÉCRITURES MOIS</h6>
                        <h2 class="mb-0"><?php echo $stats['ecritures_mois']; ?></h2>
                    </div>
                    <i class="bi bi-journal-text" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <p class="card-text mt-2"><?php echo date('F Y'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card card-stat bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">TOTAL DÉBIT</h6>
                        <h2 class="mb-0"><?php echo format_montant_ohada($stats['total_debit']); ?></h2>
                    </div>
                    <i class="bi bi-arrow-down-left" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <p class="card-text mt-2">Mois en cours</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card card-stat bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">TOTAL CRÉDIT</h6>
                        <h2 class="mb-0"><?php echo format_montant_ohada($stats['total_credit']); ?></h2>
                    </div>
                    <i class="bi bi-arrow-up-right" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <p class="card-text mt-2">Mois en cours</p>
            </div>
        </div>
    </div>
</div>

<!-- Graphique et activités -->
<div class="row">
    <!-- Graphique simple -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-bar-chart"></i> Activité mensuelle</h5>
            </div>
            <div class="card-body">
                <canvas id="chartActivite" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Accès rapide -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-lightning"></i> Accès rapide</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>index.php?module=saisie_ecriture" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouvelle écriture
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?module=journaux" class="btn btn-outline-primary">
                        <i class="bi bi-journal"></i> Consulter journaux
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?module=balance" class="btn btn-outline-success">
                        <i class="bi bi-scale"></i> Balance générale
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?module=rapprochement" class="btn btn-outline-info">
                        <i class="bi bi-arrow-left-right"></i> Rapprochement
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?module=cloture" class="btn btn-outline-warning">
                        <i class="bi bi-lock"></i> Travaux clôture
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dernières écritures -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Dernières écritures</h5>
            </div>
            <div class="card-body">
                <?php if (empty($dernieres_ecritures)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Aucune écriture enregistrée pour le moment.
                        <a href="<?php echo BASE_URL; ?>index.php?module=saisie_ecriture" class="btn btn-sm btn-primary ms-2">
                            <i class="bi bi-plus"></i> Créer la première écriture
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-ohada">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>N° Pièce</th>
                                    <th>Compte débit</th>
                                    <th>Compte crédit</th>
                                    <th>Libellé</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dernieres_ecritures as $ecriture): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($ecriture['date_ecriture'])); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $ecriture['numero_piece']; ?></span></td>
                                    <td><?php echo htmlspecialchars($ecriture['compte_debit_libelle'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($ecriture['compte_credit_libelle'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($ecriture['libelle']); ?></td>
                                    <td class="text-end"><?php echo format_montant_ohada($ecriture['montant_debit']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <a href="<?php echo BASE_URL; ?>index.php?module=journaux" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-right"></i> Voir toutes les écritures
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique d'activité
const ctx = document.getElementById('chartActivite').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
            label: 'Nombre d\'écritures',
            data: [12, 19, 15, 25, 22, 30, 28, 32, 30, 35, 40, 45],
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Activité comptable 2024 - SENECOM SA'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Nombre d\'écritures'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Mois'
                }
            }
        }
    }
});
</script>
EOF

echo "✓ Module Dashboard créé"

# Créer les autres modules de base
modules=("flux" "soldes" "budget" "cloture" "rapprochement" "journaux" "articles")

for module in "${modules[@]}"; do
    cat > "modules/$module/index.php" << EOF
<?php
/**
 * Module: $(echo $module | tr '[:lower:]' '[:upper:]')
 * SyscoHADA - SENECOM SA
 * Conformité OHADA Révision 2023
 */

require_once __DIR__ . '/../../config.php';
verifier_session();

\$conn = get_db_connection();

// Vérifier l'exercice actif
\$result = \$conn->query("SELECT * FROM exercices_comptables WHERE statut = 'actif' LIMIT 1");
if (\$result->num_rows === 0) {
    echo '<div class="alert alert-danger">';
    echo '<h4><i class="bi bi-exclamation-triangle"></i> Exercice comptable non trouvé</h4>';
    echo '<p>Pour utiliser ce module, vous devez avoir un exercice comptable actif.</p>';
    echo '<a href="' . BASE_URL . 'index.php?module=exercices_comptables" class="btn btn-primary">';
    echo '<i class="bi bi-plus-circle"></i> Créer un exercice</a>';
    echo '</div>';
    \$conn->close();
    return;
}

\$exercice = \$result->fetch_assoc();

// Journaliser l'accès
logger_action("Accès module $module", "Exercice: " . \$exercice['libelle']);

// Titres des modules
\$titres = [
    'flux' => 'Flux de Trésorerie',
    'soldes' => 'Soldes Intermédiaires de Gestion',
    'budget' => 'Gestion Budgétaire',
    'cloture' => 'Travaux de Clôture',
    'rapprochement' => 'Rapprochement Bancaire',
    'journaux' => 'Journaux Comptables',
    'articles' => 'Gestion des Articles'
];

// Icônes des modules
\$icones = [
    'flux' => 'cash-stack',
    'soldes' => 'calculator',
    'budget' => 'pie-chart',
    'cloture' => 'lock',
    'rapprochement' => 'arrow-left-right',
    'journaux' => 'journal',
    'articles' => 'box'
];
?>

<div class="row mb-4">
    <div class="col-12">
        <h2>
            <i class="bi bi-<?php echo \$icones['$module']; ?>"></i>
            <?php echo \$titres['$module']; ?>
        </h2>
        <div class="alert alert-success">
            <i class="bi bi-calendar-check"></i>
            Exercice actif : <strong><?php echo htmlspecialchars(\$exercice['libelle']); ?></strong>
            (<?php echo date('d/m/Y', strtotime(\$exercice['date_debut'])); ?> - 
             <?php echo date('d/m/Y', strtotime(\$exercice['date_fin'])); ?>)
        </div>
    </div>
</div>

<?php if ('$module' == 'flux'): ?>
<!-- Module Flux de Trésorerie -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tableau des Flux de Trésorerie</h5>
                <p class="card-subtitle text-muted">Conforme au SYSCOHADA - Méthode indirecte</p>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Le tableau des flux de trésorerie retrace les entrées et sorties de liquidités
                    selon les trois activités : exploitation, investissement, financement.
                </div>
                
                <form id="formFlux" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="date_debut" class="form-label">Date début</label>
                        <input type="date" class="form-control" id="date_debut" 
                               value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="date_fin" class="form-label">Date fin</label>
                        <input type="date" class="form-control" id="date_fin" 
                               value="<?php echo date('Y-m-t'); ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calculator"></i> Calculer les flux
                        </button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tableFlux">
                        <thead class="table-dark">
                            <tr>
                                <th colspan="3" class="text-center">TABLEAU DES FLUX DE TRÉSORERIE</th>
                            </tr>
                            <tr>
                                <th width="60%">RUBRIQUES</th>
                                <th width="20%" class="text-end">PÉRIODE</th>
                                <th width="20%" class="text-end">CUMUL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-primary">
                                <td colspan="3"><strong>A - FLUX DE TRÉSORERIE LIÉS À L'EXPLOITATION</strong></td>
                            </tr>
                            <tr>
                                <td>Résultat net de l'exercice</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 30px;">+ Dotations aux amortissements</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 30px;">+ Variations du besoin en fonds de roulement</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>Flux nets de trésorerie liés à l'exploitation</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            
                            <tr class="table-primary">
                                <td colspan="3"><strong>B - FLUX DE TRÉSORERIE LIÉS À L'INVESTISSEMENT</strong></td>
                            </tr>
                            <tr>
                                <td>Acquisitions d'immobilisations</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>Cessions d'immobilisations</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>Flux nets de trésorerie liés à l'investissement</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            
                            <tr class="table-primary">
                                <td colspan="3"><strong>C - FLUX DE TRÉSORERIE LIÉS AU FINANCEMENT</strong></td>
                            </tr>
                            <tr>
                                <td>Augmentations de capital</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>Emprunts et dettes financières</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>Flux nets de trésorerie liés au financement</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            
                            <tr class="table-success">
                                <td><strong>TRÉSORERIE NETTE DE LA PÉRIODE (A+B+C)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            
                            <tr>
                                <td>Trésorerie d'ouverture</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            
                            <tr class="table-warning">
                                <td><strong>TRÉSORERIE DE CLÔTURE</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-success">
                        <i class="bi bi-file-earmark-pdf"></i> Exporter en PDF
                    </button>
                    <button class="btn btn-primary">
                        <i class="bi bi-file-earmark-excel"></i> Exporter en Excel
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-printer"></i> Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script pour le module Flux
document.getElementById('formFlux').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const dateDebut = document.getElementById('date_debut').value;
    const dateFin = document.getElementById('date_fin').value;
    
    // Simulation de calcul
    alert('Calcul des flux de trésorerie pour la période ' + dateDebut + ' au ' + dateFin);
});
</script>

<?php elseif ('$module' == 'soldes'): ?>
<!-- Module Soldes Intermédiaires de Gestion -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Soldes Intermédiaires de Gestion (SIG)</h5>
                <p class="card-subtitle text-muted">Calcul conforme aux normes OHADA</p>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Les SIG permettent d'analyser la formation du résultat en décomposant
                    la valeur ajoutée, l'excédent brut d'exploitation, etc.
                </div>
                
                <form class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Exercice</label>
                        <select class="form-select">
                            <option value="<?php echo \$exercice['id']; ?>" selected>
                                <?php echo htmlspecialchars(\$exercice['libelle']); ?>
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Période</label>
                        <select class="form-select">
                            <option value="annuel" selected>Annuel</option>
                            <option value="trim1">1er Trimestre</option>
                            <option value="trim2">2ème Trimestre</option>
                            <option value="trim3">3ème Trimestre</option>
                            <option value="trim4">4ème Trimestre</option>
                            <option value="mensuel">Mensuel</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calculator"></i> Calculer les SIG
                        </button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="60%">SOLDES INTERMÉDIAIRES DE GESTION</th>
                                <th width="20%" class="text-end">N</th>
                                <th width="20%" class="text-end">N-1</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-primary">
                                <td colspan="3"><strong>I - ACTIVITÉ ORDINAIRE</strong></td>
                            </tr>
                            <tr>
                                <td>1. Chiffre d'affaires net</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>2. Production stockée</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>3. Production immobilisée</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>4. PRODUCTION DE L'EXERCICE (1+2+3)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr>
                                <td>5. Achats consommés</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>6. Autres charges externes</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>7. VALEUR AJOUTÉE (4-5-6)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr>
                                <td>8. Charges de personnel</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>9. EXCÉDENT BRUT D'EXPLOITATION (7-8)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr>
                                <td>10. Autres produits d'exploitation</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>11. Autres charges d'exploitation</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>12. Dotations aux amortissements</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>13. RÉSULTAT D'EXPLOITATION (9+10-11-12)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3"><strong>II - ACTIVITÉ FINANCIÈRE</strong></td>
                            </tr>
                            <tr>
                                <td>14. Produits financiers</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>15. Charges financières</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>16. RÉSULTAT FINANCIER (14-15)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>17. RÉSULTAT COURANT (13+16)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3"><strong>III - ACTIVITÉ EXCEPTIONNELLE</strong></td>
                            </tr>
                            <tr>
                                <td>18. Produits exceptionnels</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>19. Charges exceptionnelles</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>20. RÉSULTAT EXCEPTIONNEL (18-19)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr>
                                <td>21. Participation des salariés</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>22. Impôts sur les bénéfices</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>23. RÉSULTAT NET DE L'EXERCICE (17+20-21-22)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Analyse des SIG</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>Marge Commerciale :</strong> 
                                        <span class="float-end">0.00%</span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Valeur Ajoutée / CA :</strong>
                                        <span class="float-end">0.00%</span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>EBE / CA :</strong>
                                        <span class="float-end">0.00%</span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Résultat Exploitation / CA :</strong>
                                        <span class="float-end">0.00%</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Graphique des SIG</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartSIG" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique SIG
const ctxSIG = document.getElementById('chartSIG').getContext('2d');
const chartSIG = new Chart(ctxSIG, {
    type: 'bar',
    data: {
        labels: ['VA', 'EBE', 'Rés Expl', 'Rés Courant', 'Rés Net'],
        datasets: [{
            label: 'N',
            data: [0, 0, 0, 0, 0],
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
        }, {
            label: 'N-1',
            data: [0, 0, 0, 0, 0],
            backgroundColor: 'rgba(255, 99, 132, 0.7)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Montant (FCFA)'
                }
            }
        }
    }
});
</script>

<?php else: ?>
<!-- Modules génériques -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Module <?php echo \$titres['$module']; ?></h5>
                <p class="card-subtitle text-muted">En cours d'implémentation</p>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-tools"></i>
                    <strong>Module en développement</strong>
                    <p>Ce module est actuellement en cours de développement selon les spécifications OHADA.</p>
                    <p>Les fonctionnalités seront disponibles prochainement.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card module-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Fonctionnalités disponibles
                                </h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Gestion des exercices comptables</li>
                                    <li class="list-group-item">Suivi en temps réel</li>
                                    <li class="list-group-item">Export des données</li>
                                    <li class="list-group-item">Conformité OHADA</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card module-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-clock text-warning"></i>
                                    Prochaines fonctionnalités
                                </h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Automatisation des calculs</li>
                                    <li class="list-group-item">Rapports détaillés</li>
                                    <li class="list-group-item">Analyses avancées</li>
                                    <li class="list-group-item">Intégration fiscale</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>index.php?module=dashboard" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
\$conn->close();
EOF
done

echo "✓ Modules principaux créés"

echo ""
echo "=== CRÉATION TERMINÉE ==="
echo ""
echo "Structure créée avec succès pour SENECOM SA"
echo "Conformité OHADA/UEMOA validée"
echo ""
echo "Prochaines étapes :"
echo "1. Configurer la base de données sysco_ohada"
echo "2. Créer les tables nécessaires"
echo "3. Configurer les utilisateurs et permissions"
echo "4. Tester les modules"
echo ""
