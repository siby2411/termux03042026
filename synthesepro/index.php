<?php
// SYNTHESEPRO UEMOA - FRONT CONTROLLER MVC SYSCOHADA
session_start();

// Logs d'erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/synthesepro_php.log');

// Constantes chemins absolus
define('APP_ROOT', realpath(__DIR__ . '/../'));
define('APP_PATH', APP_ROOT . '/app/');
define('PUBLIC_PATH', __DIR__ . '/');

// Auto-loader PSR-4 simple
spl_autoload_register(function ($class) {
    $file = APP_ROOT . '/app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Test inclusions critiques
$required_files = [
    APP_PATH . 'Models/Db.php',
    APP_PATH . 'Models/User.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        http_response_code(500);
        die("Fichier manquant: " . basename($file));
    }
    require_once $file;
}

// Fonction rendu sécurisée
function render($view, $data = []) {
    $viewFile = APP_PATH . "Views/{$view}.php";
    if (!file_exists($viewFile)) {
        http_response_code(500);
        die("Vue manquante: {$view}");
    }
    extract($data, EXTR_SKIP);
    require $viewFile;
}

// ROUTAGE PRINCIPAL
$action = $_GET['action'] ?? 'login';
$message = '';

// === LOGIN ===
if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userModel = new User();
        $user = $userModel->authenticate($_POST['email'] ?? '', $_POST['password'] ?? '');
        
        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: ?action=home');
            exit();
        }
        $message = 'Email ou mot de passe incorrect';
    }
    render('login_form', ['message' => $message]);
    exit();
}

// === LOGOUT ===
if ($action === 'logout') {
    session_destroy();
    header('Location: ?action=login');
    exit();
}

// === AUTHENTIFICATION REQUISE ===
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit();
}

$role = $_SESSION['user_role'];

// === DASHBOARD SELON RÔLE ===
switch ($action) {
    case 'home':
        $userModel = new User();
        if ($role === 'ADMIN') {
            render('admin_dashboard');
        } elseif ($role === 'COMPTABLE') {
            $societes = $userModel->getSocietes();
            render('comptable_dashboard', ['societes' => $societes]);
        } else {
            render('lecteur_dashboard');
        }
        break;
    default:
        render('dashboard');
        break;
}
?>

