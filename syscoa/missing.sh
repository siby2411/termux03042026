#!/bin/bash
# fix_missing_constants.sh

echo "=== CORRECTION DES CONSTANTES ET FONCTIONS MANQUANTES ==="

echo "1. Ajout des constantes manquantes dans config.php..."
sudo tee -a /var/www/syscoa/config.php << 'EOF'

// ============================================
// CONSTANTES SYSCOHADA
// ============================================

define('SYSCOHADA_VERSION', '2.0');
define('SYSCOHADA_MODULES', serialize([
    'dashboard' => ['Tableau de bord', 'fas fa-tachometer-alt', 'admin'],
    'journaux' => ['Journaux', 'fas fa-book', 'comptable'],
    'grand_livre' => ['Grand Livre', 'fas fa-file-invoice-dollar', 'comptable'],
    'balance' => ['Balance', 'fas fa-scale-balanced', 'comptable'],
    'comptes' => ['Plan Comptable', 'fas fa-list', 'admin'],
    'tiers' => ['Tiers', 'fas fa-users', 'comptable'],
    'rapports' => ['Rapports', 'fas fa-chart-bar', 'comptable'],
    'etats' => ['États Financiers', 'fas fa-file-contract', 'comptable'],
    'parametres' => ['Paramètres', 'fas fa-cog', 'admin']
]));

define('SITE_NAME', 'SYSCOHADA');
define('SITE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/syscoa/');
define('DEFAULT_MODULE', 'dashboard');
define('DEFAULT_SUBMODULE', '');
define('COMPANY_NAME', 'Votre Entreprise');
define('CURRENCY', 'FCFA');
define('DATE_FORMAT', 'd/m/Y');

// ============================================
// FONCTIONS UTILITAIRES SUPPLÉMENTAIRES
// ============================================

function get_module_info($module) {
    $modules = unserialize(SYSCOHADA_MODULES);
    return $modules[$module] ?? ['Module non trouvé', 'fas fa-question-circle', 'user'];
}

function get_user_modules($role = 'user') {
    $all_modules = unserialize(SYSCOHADA_MODULES);
    $user_modules = [];
    
    foreach ($all_modules as $key => $module) {
        if ($role === 'admin' || $module[2] === $role || $module[2] === 'user') {
            $user_modules[$key] = $module;
        }
    }
    
    return $user_modules;
}

function has_module_access($module, $role = 'user') {
    $modules = unserialize(SYSCOHADA_MODULES);
    if (!isset($modules[$module])) return false;
    
    $module_role = $modules[$module][2];
    return ($role === 'admin' || $module_role === $role || $module_role === 'user');
}

function get_current_year() {
    return date('Y');
}

function get_fiscal_year() {
    $month = date('n');
    $year = date('Y');
    // Si on est après juin, année fiscale = année en cours, sinon année précédente
    return ($month >= 7) ? $year : $year - 1;
}

function debug_log($message, $data = null) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log('SYSCOHADA DEBUG: ' . $message);
        if ($data) {
            error_log(print_r($data, true));
        }
    }
}
EOF

echo "2. Vérification de la ligne 304 dans header.php..."
if [ -f "/var/www/syscoa/includes/header.php" ]; then
    echo "   Ligne 304:"
    sudo sed -n '304p' /var/www/syscoa/includes/header.php
    
    echo ""
    echo "   Contexte (lignes 300-310):"
    sudo sed -n '300,310p' /var/www/syscoa/includes/header.php
    
    # Si la ligne utilise SYSCOHADA_MODULES, vérifions comment elle est utilisée
    if sudo grep -q "SYSCOHADA_MODULES" /var/www/syscoa/includes/header.php; then
        echo ""
        echo "   Utilisations de SYSCOHADA_MODULES dans header.php:"
        sudo grep -n "SYSCOHADA_MODULES" /var/www/syscoa/includes/header.php
    fi
fi

echo ""
echo "3. Test des constantes ajoutées..."
sudo tee /var/www/syscoa/test_constants.php << 'EOF'
<?php
require_once 'config.php';

echo "<h2>Test des constantes SYSCOHADA</h2>";

echo "SYSCOHADA_VERSION: " . SYSCOHADA_VERSION . "<br>";
echo "SITE_NAME: " . SITE_NAME . "<br>";
echo "SITE_URL: " . SITE_URL . "<br>";
echo "DEFAULT_MODULE: " . DEFAULT_MODULE . "<br>";
echo "CURRENCY: " . CURRENCY . "<br>";

echo "<h3>Test des modules:</h3>";
$modules = unserialize(SYSCOHADA_MODULES);
foreach ($modules as $key => $module) {
    echo "$key: " . $module[0] . " (icon: " . $module[1] . ", role: " . $module[2] . ")<br>";
}

echo "<h3>Test de get_user_modules('admin'):</h3>";
$admin_modules = get_user_modules('admin');
foreach ($admin_modules as $key => $module) {
    echo "$key: " . $module[0] . "<br>";
}

echo "<h3>Test de has_module_access:</h3>";
echo "dashboard pour admin: " . (has_module_access('dashboard', 'admin') ? '✅' : '❌') . "<br>";
echo "dashboard pour user: " . (has_module_access('dashboard', 'user') ? '✅' : '❌') . "<br>";
echo "parametres pour user: " . (has_module_access('parametres', 'user') ? '✅' : '❌') . "<br>";
?>
EOF

echo "4. Vérification de la syntaxe PHP..."
php -l /var/www/syscoa/config.php

echo ""
echo "5. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CORRECTION APPLIQUÉE ==="
echo ""
echo "🎯 TESTS À EFFECTUER :"
echo "1. Test des constantes : http://192.168.1.33:8080/syscoa/test_constants.php"
echo "2. Connexion normale : http://192.168.1.33:8080/syscoa/login.php"
echo "3. Utilisateur : admin / Mot de passe : admin123"
echo ""
echo "📊 SI L'ERREUR PERSISTE :"
echo "   Il faut vérifier comment SYSCOHADA_MODULES est utilisé dans header.php"
