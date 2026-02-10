#!/bin/bash
# create_minimal_working_env.sh

echo "=== CRÉATION D'UN ENVIRONNEMENT MINIMAL FONCTIONNEL ==="

# 1. Sauvegarder l'existant
echo "1. Sauvegarde des fichiers existants..."
sudo cp /var/www/syscoa/index.php /var/www/syscoa/index.php.backup2
sudo cp /var/www/syscoa/login.php /var/www/syscoa/login.php.backup2

# 2. Créer un config.php éprouvé
echo "2. Configuration de config.php..."
sudo tee /var/www/syscoa/config.php << 'EOF'
<?php
// Configuration SYSCOHADA MINIMAL - GARANTIE FONCTIONNEL
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gestion de session robuste
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'sysco_ohada');
define('DB_USER', 'root');
define('DB_PASS', '123');

// Connexion PDO sécurisée
function get_db_connection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}

// Vérification de connexion SIMPLE
function check_login() {
    if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }
}

// Alias pour compatibilité
function check_auth() { check_login(); }
?>
EOF

# 3. Créer un login.php minimal éprouvé
echo "3. Création de login.php minimal..."
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
<html>
<head>
    <title>Login SYSCOHADA</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 50px; }
        .login-box { background: white; padding: 30px; border-radius: 10px; max-width: 400px; margin: auto; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>SYSCOHADA - Connexion</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Utilisateur" value="admin" required>
            <input type="password" name="password" placeholder="Mot de passe" value="admin123" required>
            <button type="submit">Se connecter</button>
        </form>
        <p><small>Utilisateur: admin / Mot de passe: admin123</small></p>
    </div>
</body>
</html>
EOF

# 4. Créer un index.php minimal éprouvé
echo "4. Création de index.php minimal..."
sudo tee /var/www/syscoa/index.php << 'EOF'
<?php
// INDEX.PHP MINIMAL FONCTIONNEL
require_once 'config.php';
check_login();

// Inclure header si existe, sinon créer basique
if (file_exists('includes/header.php')) {
    include 'includes/header.php';
} else {
    echo '<!DOCTYPE html><html><head><title>SYSCOHADA</title></head><body>';
    echo '<div style="padding:20px;">';
}

echo '<h1>Tableau de bord SYSCOHADA</h1>';
echo '<p>Bienvenue, ' . htmlspecialchars($_SESSION['username']) . '!</p>';

// Vérifier la connexion à la base
try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo '<p>✅ Base de données connectée (' . $result['count'] . ' utilisateurs)</p>';
} catch (Exception $e) {
    echo '<p>❌ Erreur base: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '<h3>Menu rapide:</h3>';
echo '<ul>';
echo '<li><a href="?page=dashboard">Tableau de bord</a></li>';
echo '<li><a href="?page=journaux">Journaux</a></li>';
echo '<li><a href="?page=grand-livre">Grand livre</a></li>';
echo '<li><a href="logout.php">Déconnexion</a></li>';
echo '</ul>';

// Inclure footer si existe
if (file_exists('includes/footer.php')) {
    include 'includes/footer.php';
} else {
    echo '</div></body></html>';
}
EOF

# 5. Créer un header.php minimal si nécessaire
echo "5. Vérification de header.php..."
if [ ! -f "/var/www/syscoa/includes/header.php" ]; then
    sudo mkdir -p /var/www/syscoa/includes
    sudo tee /var/www/syscoa/includes/header.php << 'EOF'
<?php
// HEADER MINIMAL
if (!isset($no_header)) {
    echo '<!DOCTYPE html><html><head>';
    echo '<title>SYSCOHADA</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body>';
    echo '<nav class="navbar navbar-dark bg-primary">';
    echo '<div class="container">';
    echo '<a class="navbar-brand" href="index.php">SYSCOHADA</a>';
    echo '<span class="text-white">' . htmlspecialchars($_SESSION['username'] ?? '') . '</span>';
    echo '</div></nav>';
    echo '<div class="container mt-4">';
}
?>
EOF
fi

# 6. Créer une page logout.php
echo "6. Création de logout.php..."
sudo tee /var/www/syscoa/logout.php << 'EOF'
<?php
session_start();
session_destroy();
header('Location: login.php');
exit();
?>
EOF

# 7. Tester la syntaxe
echo "7. Test de syntaxe PHP..."
for file in /var/www/syscoa/config.php /var/www/syscoa/login.php /var/www/syscoa/index.php; do
    echo "   $(basename $file): $(php -l $file 2>/dev/null | grep -o 'No syntax errors' || echo 'ERREUR')"
done

# 8. Redémarrer Apache
echo "8. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== ENVIRONNEMENT MINIMAL CRÉÉ ==="
echo ""
echo "🎯 ACCÈS : http://192.168.1.33:8080/syscoa/"
echo ""
echo "📋 TESTS À EFFECTUER :"
echo "1. Accédez à l'URL ci-dessus"
echo "2. Connectez-vous avec admin / admin123"
echo "3. Vous devriez voir le tableau de bord minimal"
echo ""
echo "🔧 SI ÇA MARCHE :"
echo "   Vous pouvez ensuite restaurer progressivement vos fichiers"
echo ""
echo "📊 SI ÉCHEC :"
echo "   Consultez : sudo tail -f /var/log/apache2/error.log"
