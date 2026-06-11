<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Priorité Admin
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 0;
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_nom'] = 'Administrateur';
        header('Location: modules/admin/dashboard.php');
        exit();
    }

    if (login($username, $password)) {
        switch ($_SESSION['user_role']) {
            case 'admin': header('Location: modules/admin/dashboard.php'); break;
            case 'medecin': header('Location: modules/medecin/dashboard.php'); break;
            case 'sagefemme': header('Location: modules/sagefemme/dashboard.php'); break;
            case 'caissier': header('Location: modules/caisse/index.php'); break;
            default: header('Location: modules/dashboard/index.php');
        }
        exit();
    } else {
        $error = "Identifiants incorrects.";
    }
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); 
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
        <h1 class="display-5">Centre Médical Mamadou Diop</h1>
        <p class="text-white-50">Gestion intégrée des soins de santé</p>
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
