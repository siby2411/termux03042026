<?php
// index.php - Point d'entrée unique
session_start();
define('ROOT_PATH', dirname(__FILE__));

// Configuration
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

// Vérifier authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Module par défaut
$modules_available = [
    'dashboard' => 'Tableau de bord',
    'soldes' => 'Soldes intermédiaires',
    'rapprochement' => 'Rapprochement bancaire',
    'articles' => 'Gestion articles',
    'cloture' => 'Travaux clôture',
    'flux' => 'Flux trésorerie',
    'ecritures' => 'Écritures',
    'grand_livre' => 'Grand livre',
    'journaux' => 'Journaux',
    'balance' => 'Balance',
    'bilans' => 'Bilans',
    'compte_resultat' => 'Compte résultat',
    'ratios' => 'Ratios',
    'inventaire' => 'Inventaire',
    'amortissements' => 'Amortissements',
    'provisions' => 'Provisions',
    'rapports' => 'Rapports',
    'admin' => 'Administration'
];

$module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
if (!array_key_exists($module, $modules_available)) {
    $module = 'dashboard';
}

// Inclure le header
include ROOT_PATH . '/partials/header.php';

// Inclure la sidebar
include ROOT_PATH . '/partials/sidebar.php';

// Contenu principal
echo '<main class="main-content">';
echo '<div class="container-fluid">';

// Barre de navigation
echo '<nav aria-label="breadcrumb" class="mb-4">';
echo '<ol class="breadcrumb">';
echo '<li class="breadcrumb-item"><a href="?module=dashboard">Accueil</a></li>';
echo '<li class="breadcrumb-item active">' . $modules_available[$module] . '</li>';
echo '</ol>';
echo '</nav>';

// Charger le module
$module_file = ROOT_PATH . '/modules/' . $module . '.php';
if (file_exists($module_file)) {
    include $module_file;
} else {
    echo '<div class="alert alert-warning">';
    echo '<h4><i class="fas fa-exclamation-triangle"></i> Module non disponible</h4>';
    echo '<p>Le module "' . $modules_available[$module] . '" n\'est pas encore implémenté.</p>';
    echo '</div>';
}

echo '</div>';
echo '</main>';

// Inclure le footer
include ROOT_PATH . '/partials/footer.php';
?>
