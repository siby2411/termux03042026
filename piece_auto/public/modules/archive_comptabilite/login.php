<?php
session_start();

// Correction du chemin - config.php est dans le dossier config/
require_once dirname(__DIR__) . '/config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($email === '' || $password === '') {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM USERS WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirection vers dashboard_expert.php
                header("Location: dashboard_expert.php");
                exit();
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error = "Erreur technique. Veuillez réessayer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - OMEGA INFORMATIQUE CONSULTING ERP SYSCOHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0f3b52 0%, #1a4a6f 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .login-body {
            padding: 2rem;
        }
        .btn-login {
            background: linear-gradient(135deg, #0f3b52 0%, #1a4a6f 100%);
            border: none;
            border-radius: 50px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-journal-bookmark-fill fs-1"></i>
                        <h3 class="mt-2 mb-0">OMEGA INFORMATIQUE CONSULTING ERP</h3>
                        <small>SYSCOHADA UEMOA - Mohamet Siby</small>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-envelope"></i> Email professionnel
                                </label>
                                <input type="email" name="email" class="form-control form-control-lg" 
                                       placeholder="ex: admin@synthesepro.com" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-lock"></i> Mot de passe
                                </label>
                                <input type="password" name="password" class="form-control form-control-lg" 
                                       placeholder="password" required>
                            </div>
                            <button type="submit" class="btn btn-login btn-primary w-100 btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Se connecter
                            </button>
                        </form>
                        
                        <div class="info-card">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> <strong>Accès démo :</strong><br>
                                📧 admin@synthesepro.com<br>
                                🔑 password
                            </small>
                        </div>
                    </div>
                </div>
                <p class="text-center text-white-50 mt-3 small">
                    <i class="bi bi-shield-check"></i> OMEGA INFORMATIQUE CONSULTING
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
