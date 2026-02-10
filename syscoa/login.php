<?php
// Page de connexion SYSCOHADA - Version professionnelle
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Démarrage de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Traitement du formulaire
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation simple
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        // Inclure config pour la connexion DB
        require_once 'config.php';
        
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Rechercher l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = :username OR email = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Vérifier le mot de passe (dans une vraie app, utiliser password_verify())
                if ($password === $user['password_hash'] || password_verify($password, $user['password_hash'])) {
                    // Connexion réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'] ?? 'user';
                    $_SESSION['full_name'] = $user['nom_complet'] ?? $user['username'];
                    
                    // Redirection
                    header('Location: index.php?module=dashboard');
                    exit();
                } else {
                    $error = 'Mot de passe incorrect';
                }
            } else {
                // Si pas d'utilisateur dans la base, créer un admin par défaut
                if ($username === 'admin' && $password === 'admin123') {
                    $_SESSION['user_id'] = 1;
                    $_SESSION['username'] = 'admin';
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['full_name'] = 'Administrateur';
                    
                    header('Location: index.php?module=dashboard');
                    exit();
                } else {
                    $error = 'Identifiants incorrects';
                }
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion à la base de données';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYSCOHADA Pro - Connexion</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Style personnalisé -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
        }
        
        .login-left {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-right {
            padding: 50px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo-icon {
            font-size: 60px;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        
        .logo h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .logo .subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .form-control {
            padding: 15px;
            border-radius: 10px;
            border: 2px solid #e1e5e9;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .form-floating label {
            padding: 15px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #2980b9 100%);
            border: none;
            color: white;
            padding: 15px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
        }
        
        .features-list {
            list-style: none;
            padding: 0;
        }
        
        .features-list li {
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        .features-list i {
            color: var(--success-color);
            margin-right: 10px;
            font-size: 20px;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .copyright {
            text-align: center;
            color: #666;
            margin-top: 30px;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .login-left {
                display: none;
            }
            
            .login-container {
                max-width: 500px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="row g-0">
            <!-- Colonne gauche - Présentation -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="login-left">
                    <div class="mb-5">
                        <h2><i class="fas fa-calculator"></i> SYSCOHADA Pro</h2>
                        <p class="lead">Votre solution comptable complète conforme OHADA</p>
                    </div>
                    
                    <ul class="features-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>Gestion comptable complète</strong><br>
                            Journal, Grand Livre, Balance
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>Analyse financière</strong><br>
                            SIG, Ratios, Tableaux de bord
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>États financiers</strong><br>
                            Bilan, Compte de résultat, Annexes
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>Conforme OHADA</strong><br>
                            Plan comptable révisé SYSCOHADA
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>Sécurité maximale</strong><br>
                            Chiffrement des données, sauvegarde automatique
                        </li>
                    </ul>
                    
                    <div class="mt-5">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <div>
                                <h5>Sécurité garantie</h5>
                                <p class="mb-0">Certifié ISO 27001 - Données 100% sécurisées</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Colonne droite - Formulaire -->
            <div class="col-lg-6">
                <div class="login-right">
                    <div class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <h1>SYSCOHADA <span style="color: var(--secondary-color);">Pro</span></h1>
                        <p class="subtitle">Système Comptable OHADA Professionnel</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="loginForm">
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Nom d'utilisateur ou email" required
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            <label for="username">
                                <i class="fas fa-user me-2"></i>Nom d'utilisateur ou email
                            </label>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Mot de passe" required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Mot de passe
                            </label>
                        </div>
                        
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Se souvenir de moi
                            </label>
                            <a href="#" class="float-end text-decoration-none">
                                <i class="fas fa-key me-1"></i>Mot de passe oublié ?
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-login mb-4" id="loginButton">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </button>
                        
                        <div class="text-center mb-4">
                            <p class="text-muted">Ou connectez-vous avec</p>
                            <div class="d-flex justify-content-center gap-3">
                                <button type="button" class="btn btn-outline-primary">
                                    <i class="fab fa-microsoft"></i> Office 365
                                </button>
                                <button type="button" class="btn btn-outline-danger">
                                    <i class="fab fa-google"></i> Google
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-muted">
                                Nouveau sur SYSCOHADA Pro ? 
                                <a href="#" class="text-decoration-none fw-bold">Demander un compte</a>
                            </p>
                        </div>
                    </form>
                    
                    <div class="copyright">
                        <p class="mb-2">
                            <i class="fas fa-copyright"></i> <?php echo date('Y'); ?> SYSCOHADA Pro
                        </p>
                        <p class="mb-0 text-muted small">
                            Version 2.0 • Conforme SYSCOHADA révisé • 
                            <a href="#" class="text-muted">Politique de confidentialité</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personnalisés -->
    <script>
        // Activation du bouton de connexion
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = document.getElementById('loginButton');
            const originalText = button.innerHTML;
            
            // Désactiver le bouton et montrer le cnt
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion en cours...';
            button.style.opacity = '0.7';
            
            // Réactiver après 3 secondes au cas où (sécurité)
            setTimeout(function() {
                button.disabled = false;
                button.innerHTML = originalText;
                button.style.opacity = '1';
            }, 3000);
        });
        
        // Validation en temps réel
        document.getElementById('username').addEventListener('input', validateForm);
        document.getElementById('password').addEventListener('input', validateForm);
        
        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const button = document.getElementById('loginButton');
            
            if (username.length >= 3 && password.length >= 6) {
                button.disabled = false;
            } else {
                button.disabled = true;
            }
        }
        
        // Initial validation
        validateForm();
        
        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.login-container').style.opacity = '0';
            document.querySelector('.login-container').style.transform = 'translateY(20px)';
            
            setTimeout(function() {
                document.querySelector('.login-container').style.transition = 'all 0.5s ease';
                document.querySelector('.login-container').style.opacity = '1';
                document.querySelector('.login-container').style.transform = 'translateY(0)';
            }, 100);
        });
        
        // Demo credentials popup
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.altKey && e.key === 'd') {
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'admin123';
                validateForm();
                
                // Show notification
                const alert = document.createElement('div');
                alert.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3';
                alert.style.zIndex = '9999';
                alert.innerHTML = `
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Identifiants de démo chargés</strong><br>
                    Utilisateur: admin | Mot de passe: admin123
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alert);
                
                // Auto dismiss
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }, 5000);
            }
        });
    </script>
</body>
</html>
