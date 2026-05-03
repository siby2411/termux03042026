<?php
session_start();

// Inclure la configuration de la base de données en premier
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        // Rediriger vers le dashboard approprié selon le rôle
        $role = $_SESSION['user_role'];
        switch ($role) {
            case 'admin':
                header('Location: modules/admin/dashboard.php');
                break;
            case 'doctor':
                header('Location: modules/doctor/dashboard.php');
                break;
            case 'nurse':
                header('Location: modules/nurse/dashboard.php');
                break;
            case 'cashier':
                header('Location: modules/cashier/dashboard.php');
                break;
            default:
                header('Location: modules/dashboard/index.php');
        }
        exit();
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omega Informatique - Centre de Santé Mamadou Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Omega Informatique</h4>
                        <h5>Gestion Centre de Santé Mamadou Diop</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur :</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe :</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
