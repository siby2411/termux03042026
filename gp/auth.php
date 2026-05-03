<?php
session_start();
$admin_password = 'admin123';
if (!isset($_SESSION['admin_logged'])) {
    if (isset($_POST['mdp']) && $_POST['mdp'] === $admin_password) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_login_time'] = time();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        echo '<!DOCTYPE html>
        <html><head><meta charset="UTF-8"><title>Connexion admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-family: "Segoe UI", Arial; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-card { background: white; border-radius: 20px; padding: 40px; width: 350px; box-shadow: 0 20px 35px rgba(0,0,0,0.2); text-align: center; }
            .login-card i { font-size: 60px; color: #ff8c00; margin-bottom: 20px; }
            .login-card h2 { margin-bottom: 25px; color: #333; }
            .login-card input { width: 90%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 30px; font-size: 16px; }
            .login-card button { background: #ff8c00; color: white; border: none; padding: 12px 20px; border-radius: 30px; cursor: pointer; width: 100%; font-size: 16px; }
            .login-card button:hover { background: #e65c00; }
        </style>
        </head><body>
        <div class="login-card">
            <i class="fas fa-lock"></i>
            <h2>Espace administrateur</h2>
            <form method="post">
                <input type="password" name="mdp" placeholder="Mot de passe" required autofocus>
                <button type="submit"><i class="fas fa-sign-in-alt"></i> Se connecter</button>
            </form>
        </div></body></html>';
        exit;
    }
}
?>
