<?php
session_start();
// Si vous avez besoin de la base de données, décommentez la ligne suivante :
// require_once '../config/config.php'; 

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Priorité Admin (Bypass pour tests)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'admin';
        header('Location: index.php');
        exit();
    }
    
    $error = "Identifiants incorrects.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | Omega Informatique Consulting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; min-height: 100vh; display: flex; flex-direction: column; }
        .top-bar { 
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%); 
            color: #fff; 
            padding: 80px 20px; 
            text-align: center; 
        }
        .login-wrapper {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: -100px;
        }
        .login-card { 
            background: white; 
            border-radius: 20px; 
            padding: 40px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.15); 
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="small text-uppercase mb-2" style="letter-spacing: 2px;">Omega Informatique Consulting</div>
        <h1 class="display-5">Gestion Pièces Auto</h1>
        <p class="text-white-50">Interface de Gestion des Stocks</p>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <h4 class="text-center mb-4">Connexion</h4>
            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Identifiant</label>
                    <input type="text" name="username" class="form-control" value="admin" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" value="admin123" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3 py-2">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>
