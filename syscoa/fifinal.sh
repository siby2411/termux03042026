#!/bin/bash
# fix_config_final.sh

echo "=== RÉPARATION FINALE DE CONFIG.PHP ==="

echo "1. Sauvegarde des fichiers..."
sudo cp /var/www/syscoa/config.php /var/www/syscoa/config.php.backup.final
sudo cp /var/www/syscoa/login.php /var/www/syscoa/login.php.backup.final 2>/dev/null || true

echo "2. Création du nouveau config.php..."
sudo tee /var/www/syscoa/config.php << 'EOF'
<?php
// Configuration SYSCOHADA - Version finale
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

function get_module_title($module) {
    $modules = unserialize(SYSCOHADA_MODULES);
    return $modules[$module][0] ?? 'Module';
}

function get_module_icon($module) {
    $modules = unserialize(SYSCOHADA_MODULES);
    return $modules[$module][1] ?? 'fas fa-question-circle';
}
?>
EOF

echo "3. Vérification de login.php..."
# Vérifier si login.php a du code en dehors des balises PHP
if grep -q "^function " /var/www/syscoa/login.php; then
    echo "   ❌ login.php semble corrompu, restauration..."
    # Créer un login.php propre
    sudo tee /var/www/syscoa/login.php << 'EOF'
<?php
session_start();
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = 'admin';
        header('Location: index.php');
        exit();
    } else {
        $error = 'Identifiants incorrects';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SYSCOHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .login-container { max-width: 400px; margin: 100px auto; }
        .card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #2c3e50, #3498db); color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header text-center py-4">
                    <h3><i class="fas fa-balance-scale"></i> SYSCOHADA</h3>
                    <p class="mb-0">Système Comptable OHADA</p>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="admin" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p class="text-muted small mb-0">
                            <strong>Identifiants:</strong><br>
                            Utilisateur: <code>admin</code> | Mot de passe: <code>admin123</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
EOF
fi

echo "4. Vérification de la syntaxe..."
php -l /var/www/syscoa/config.php
php -l /var/www/syscoa/login.php

echo "5. Nettoyage des fichiers temporaires..."
sudo rm -f /var/www/syscoa/test_*.php 2>/dev/null

echo "6. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== RÉPARATION TERMINÉE ==="
echo ""
echo "🎯 TESTS À EFFECTUER :"
echo "1. Page de login : http://192.168.1.33:8080/syscoa/login.php"
echo "   → Ne devrait plus afficher de code PHP"
echo ""
echo "2. Connexion avec admin/admin123"
echo "   → Devrait rediriger vers le tableau de bord"
echo ""
echo "3. Navigation entre les modules"
echo "   → Tous les modules devraient fonctionner"
echo ""
echo "📊 SI PROBLÈME :"
echo "   sudo tail -f /var/log/apache2/error.log"
