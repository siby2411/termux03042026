<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM utilisateurs_hotel WHERE username = ? AND actif = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - OMEGA Hôtel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 30px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .btn-login {
            background: #e94560;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #ff6b6b;
            transform: translateY(-2px);
        }
        .hotel-icon {
            font-size: 3rem;
            color: #e94560;
        }
        .debug-info {
            font-size: 0.7rem;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="fas fa-hotel hotel-icon"></i>
            <h3 class="mt-2">OMEGA Hôtel</h3>
            <p class="text-muted">Connexion à l'espace de gestion</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label><i class="fas fa-user me-1"></i> Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" required autofocus value="admin">
            </div>
            <div class="mb-3">
                <label><i class="fas fa-lock me-1"></i> Mot de passe</label>
                <input type="password" name="password" class="form-control" required value="admin123">
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Se connecter
            </button>
        </form>
        <div class="text-center mt-3 small text-muted">
            <i class="fas fa-key"></i> admin / admin123
        </div>
        <div class="text-center mt-2">
            <a href="../index.php" class="text-decoration-none small">← Retour au portail principal</a>
        </div>
        <div class="debug-info">
            <small>🔧 Mode debug: Cliquez sur le bouton pour tester la connexion</small>
        </div>
    </div>
</body>
</html>
