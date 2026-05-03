<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        // Redirection selon le rôle
        switch ($_SESSION['user_role']) {
            case 'admin':
                header('Location: modules/admin/dashboard.php');
                break;
            case 'medecin':
                header('Location: modules/medecin/dashboard.php');
                break;
            case 'sagefemme':
                header('Location: modules/sagefemme/dashboard.php');
                break;
            case 'caissier':
                header('Location: modules/caisse/index.php');
                break;
            default:
                header('Location: modules/dashboard/index.php');
        }
        exit();
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Centre Mamadou Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; border-radius: 15px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 class="text-center mb-4">Centre Mamadou Diop</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Nom d'utilisateur" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>
</body>
</html>
