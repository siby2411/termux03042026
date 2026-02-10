#!/bin/bash
# fix_all_config_issues.sh

echo "=== CORRECTION COMPLÈTE DE LA CONFIGURATION ==="

echo "1. Création d'un nouveau config.php propre..."
sudo tee /var/www/syscoa/config.php << 'EOF'
<?php
// Configuration SYSCOHADA
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'sysco_ohada');
define('DB_USER', 'root');
define('DB_PASS', '123');

function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Erreur DB: " . $e->getMessage());
        }
    }
    return $pdo;
}

function check_login() {
    if (empty($_SESSION['logged_in'])) {
        header('Location: login.php');
        exit();
    }
}

function check_auth() { check_login(); }

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

function generate_menu($current_module = 'dashboard') {
    $modules = unserialize(SYSCOHADA_MODULES);
    $html = '';
    foreach ($modules as $key => $module) {
        $active = ($current_module == $key) ? ' active' : '';
        $html .= '<a class="nav-link' . $active . '" href="index.php?module=' . $key . '">';
        $html .= '<i class="' . $module[1] . '"></i> ' . $module[0];
        $html .= '</a>';
    }
    return $html;
}

function has_module_access($module, $role = 'user') {
    $modules = unserialize(SYSCOHADA_MODULES);
    if (!isset($modules[$module])) return false;
    return ($role === 'admin' || $modules[$module][2] === $role || $modules[$module][2] === 'user');
}
?>
EOF

echo "2. Correction de header.php pour utiliser unserialize()..."
if [ -f "/var/www/syscoa/includes/header.php" ]; then
    sudo cp /var/www/syscoa/includes/header.php /var/www/syscoa/includes/header.php.backup2
    
    # Remplacer SYSCOHADA_MODULES[ par unserialize(SYSCOHADA_MODULES)[
    sudo sed -i 's/SYSCOHADA_MODULES\[/unserialize(SYSCOHADA_MODULES)[/g' /var/www/syscoa/includes/header.php
    
    echo "   ✅ header.php corrigé"
fi

echo "3. Création d'un fichier de test..."
sudo tee /var/www/syscoa/test_config.php << 'EOF'
<?php
require_once 'config.php';

echo "<h1>Test de configuration SYSCOHADA</h1>";
echo "Version: " . SYSCOHADA_VERSION . "<br>";
echo "Site: " . SITE_NAME . "<br>";

$modules = unserialize(SYSCOHADA_MODULES);
echo "<h3>Modules disponibles:</h3>";
foreach ($modules as $key => $module) {
    echo "$key: " . $module[0] . "<br>";
}

echo "<h3>Test de generate_menu():</h3>";
echo generate_menu('dashboard');

echo "<h3>Test de connexion DB:</h3>";
try {
    $pdo = get_db_connection();
    echo "✅ Connexion DB réussie<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ " . $result['count'] . " utilisateur(s) trouvé(s)<br>";
} catch (Exception $e) {
    echo "❌ Erreur DB: " . $e->getMessage() . "<br>";
}
?>
EOF

echo "4. Vérification de la syntaxe..."
echo "   config.php: $(php -l /var/www/syscoa/config.php 2>&1 | grep -o 'No syntax errors' || echo 'ERREUR')"
echo "   header.php: $(php -l /var/www/syscoa/includes/header.php 2>&1 | grep -o 'No syntax errors' || echo 'ERREUR')"

echo "5. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CORRECTION TERMINÉE ==="
echo ""
echo "🎯 TESTS À EFFECTUER :"
echo "1. Test de config: http://192.168.1.33:8080/syscoa/test_config.php"
echo "2. Connexion: http://192.168.1.33:8080/syscoa/login.php"
echo "3. Utilisateur: admin / Mot de passe: admin123"
echo ""
echo "📊 SI PROBLÈME :"
echo "   sudo tail -f /var/log/apache2/error.log"
