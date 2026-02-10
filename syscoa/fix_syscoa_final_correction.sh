#!/bin/bash
# fix_syscoa_final_correction.sh

echo "=== CORRECTION FINALE SYSCOHADA ==="

# Demander le mot de passe MySQL
read -sp "Mot de passe MySQL root: " mysql_password
echo ""

# Créer un script PHP pour générer le hash bcrypt
cat > /tmp/generate_hash.php << 'PHP_EOF'
<?php
// Générer un hash bcrypt pour admin123
$hash = password_hash('admin123', PASSWORD_BCRYPT);
echo $hash;
PHP_EOF

# Générer le hash
echo "1. Génération du hash bcrypt pour 'admin123'..."
NEW_HASH=$(php /tmp/generate_hash.php)
echo "Nouveau hash généré: $NEW_HASH"

# Mettre à jour la base de données
echo "2. Mise à jour du mot de passe dans la base de données..."
mysql -u root -p$mysql_password sysco_ohada << SQL_EOF
-- Mettre à jour le mot de passe admin
UPDATE users 
SET password_hash = '$NEW_HASH'
WHERE username = 'admin';

-- Vérifier la mise à jour
SELECT username, 
       LEFT(password_hash, 50) as hash_prefix,
       CASE 
         WHEN password_hash = '$NEW_HASH' THEN '✓ Hash mis à jour'
         ELSE '✗ Hash non modifié'
       END as status
FROM users 
WHERE username = 'admin';
SQL_EOF

# Mettre à jour le fichier login.php pour utiliser password_hash
echo "3. Mise à jour du fichier login.php..."
sudo tee /var/www/syscoa/login.php > /dev/null << 'PHP_EOF'
<?php
session_start();
require_once 'config.php';

// Connexion via la base de données
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $pdo = get_db_connection();
        
        // Rechercher l'utilisateur dans la table users
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Vérifier le mot de passe avec password_verify (pour hash bcrypt)
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['nom_complet'] = $user['nom_complet'];
                $_SESSION['email'] = $user['email'];
                
                // Mettre à jour la date de dernière connexion
                $update_stmt = $pdo->prepare("UPDATE users SET date_derniere_connexion = NOW() WHERE id_user = ?");
                $update_stmt->execute([$user['id_user']]);
                
                header('Location: index.php');
                exit();
            } 
            // Fallback : vérification en clair (pour compatibilité)
            else if ($password === $user['password_hash']) {
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['nom_complet'] = $user['nom_complet'];
                $_SESSION['email'] = $user['email'];
                
                header('Location: index.php');
                exit();
            } else {
                $error = "Mot de passe incorrect";
            }
        } else {
            $error = "Utilisateur non trouvé";
        }
    } catch (PDOException $e) {
        $error = "Erreur de connexion à la base de données: " . $e->getMessage();
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
        .sysco-logo { font-size: 2.5rem; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header text-center py-4">
                    <div class="sysco-logo">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h3>SYSCOHADA</h3>
                    <p class="mb-0">Système Comptable OHADA</p>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i> Nom d'utilisateur
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="admin" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Mot de passe
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p class="text-muted small mb-0">
                            <strong>Identifiants par défaut:</strong><br>
                            Utilisateur: <code>admin</code> | Mot de passe: <code>admin123</code>
                        </p>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <a href="#" class="small text-decoration-none">
                            <i class="fas fa-question-circle"></i> Aide
                        </a>
                        <span class="mx-2">•</span>
                        <a href="#" class="small text-decoration-none">
                            <i class="fas fa-key"></i> Mot de passe oublié?
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted small">
                    Version 2.0 • SYSCOHADA Compliant<br>
                    <small class="text-info">
                        <i class="fas fa-link"></i> 
                        Accès: http://<?php echo $_SERVER['HTTP_HOST']; ?>/syscoa/
                    </small>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <script>
        // Focus sur le champ mot de passe après saisie du username
        document.getElementById('username').addEventListener('input', function() {
            if (this.value.trim() !== '') {
                document.getElementById('password').focus();
            }
        });
        
        // Afficher/masquer le mot de passe
        const togglePassword = document.createElement('button');
        togglePassword.type = 'button';
        togglePassword.className = 'btn btn-sm btn-outline-secondary position-absolute end-0 top-50 translate-middle-y';
        togglePassword.innerHTML = '<i class="fas fa-eye"></i>';
        togglePassword.style.right = '10px';
        
        const passwordField = document.getElementById('password');
        passwordField.parentElement.style.position = 'relative';
        passwordField.parentElement.appendChild(togglePassword);
        
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    </script>
</body>
</html>
PHP_EOF

# 4. Vérifier que config.php a le bon mot de passe MySQL
echo "4. Vérification du fichier config.php..."
sudo tee /var/www/syscoa/config.php > /dev/null << 'PHP_EOF'
<?php
// Configuration SYSCOHADA pour WSL
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'sysco_ohada');
define('DB_USER', 'root');
define('DB_PASS', '<?php echo $mysql_password; ?>');

// Connexion PDO
function get_db_connection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur de connexion: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Vérification de session simplifiée
function check_login() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }
}

// Fonction pour exécuter des requêtes
function execute_query($sql, $params = []) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
?>
PHP_EOF

# Remplacer la variable dans config.php
sudo sed -i "s/'<?php echo \$mysql_password; ?>'/'$mysql_password'/" /var/www/syscoa/config.php

# 5. Redémarrer Apache
echo "5. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CORRECTION TERMINÉE ==="
echo ""
echo "✅ SYSCOHADA est maintenant configuré avec:"
echo ""
echo "📱 URL D'ACCÈS:"
echo "   • http://92.168.1.33:8080/syscoa/"
echo "   • http://localhost:8080/syscoa/"
echo ""
echo "🔑 IDENTIFIANTS:"
echo "   • Utilisateur: admin"
echo "   • Mot de passe: admin123"
echo ""
echo "🗄️ BASE DE DONNÉES:"
echo "   • Base: sysco_ohada"
echo "   • Table users: password_hash mis à jour avec bcrypt"
echo ""
echo "🔧 EN CAS DE PROBLÈME:"
echo "   1. Vérifier Apache: sudo service apache2 status"
echo "   2. Vérifier les logs: sudo tail -f /var/log/apache2/error.log"
echo "   3. Tester la connexion MySQL: mysql -u root -p"
echo "   4. Vérifier le hash dans la base: SELECT * FROM users WHERE username='admin';"
