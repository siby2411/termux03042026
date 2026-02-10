<?php
// Index SYSCOHADA - Version simplifiée et sécurisée
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Inclure la configuration
if (!file_exists('config.php')) {
    die("ERREUR: Fichier config.php introuvable");
}
require_once 'config.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Module courant
$current_module = 'dashboard';
if (isset($_GET['module'])) {
    $module_param = $_GET['module'];
    if (is_array($module_param)) {
        $current_module = is_array($module_param) && !empty($module_param[0]) ? $module_param[0] : 'dashboard';
    } else {
        $current_module = $module_param;
    }
}

// Nettoyer le nom du module
$current_module = preg_replace('/[^a-z_]/', '', strtolower($current_module));

// Inclure le header
include 'includes/header.php';

// Chercher le fichier de module
$module_file = null;

// Chercher dans pages/ d'abord
if (file_exists("pages/$current_module.php")) {
    $module_file = "pages/$current_module.php";
} 
// Sinon chercher dans modules/
elseif (file_exists("modules/$current_module.php")) {
    $module_file = "modules/$current_module.php";
}

// Afficher le module ou un message d'erreur
if ($module_file && file_exists($module_file)) {
    include $module_file;
} else {
    echo '<div class="alert alert-danger">';
    echo '<h4><i class="fas fa-exclamation-triangle"></i> Module non disponible</h4>';
    echo '<p>Le module <strong>' . htmlspecialchars($current_module) . '</strong> n\'est pas encore implémenté.</p>';
    
    // Liste des modules disponibles
    $available_pages = glob('pages/*.php');
    if (count($available_pages) > 0) {
        echo '<p>Modules disponibles:</p><ul>';
        foreach ($available_pages as $page) {
            $name = basename($page, '.php');
            echo '<li><a href="?module=' . $name . '">' . $name . '</a></li>';
        }
        echo '</ul>';
    }
    
    echo '<a href="?module=dashboard" class="btn btn-primary">Retour au tableau de bord</a>';
    echo '</div>';
}

// Inclure le footer
include 'includes/footer.php';
