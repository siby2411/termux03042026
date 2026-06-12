<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($username, $password)) {
        switch ($_SESSION['user_role']) {
            case 'admin': header('Location: modules/admin/dashboard.php'); break;
            case 'secretariat': header('Location: modules/secretariat/dashboard.php'); break;
            case 'sagefemme': header('Location: modules/sagefemme/dashboard.php'); break;
            case 'caissier': header('Location: modules/caisse/index.php'); break;
            case 'medecin': header('Location: modules/medecin/dashboard.php'); break;
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
    <title>Connexion | Centre Mamadou Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; flex-direction: column; align-items: center; }
        .banner {
            width: 100%;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #00d2ff 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .banner h1 { margin: 0; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .banner p { margin: 5px 0 0; opacity: 0.9; font-style: italic; }
        .card { width: 100%; max-width: 400px; border-radius: 15px; }
    </style>
</head>
<body>
    <div class="banner">
        <h1>Centre Médical Mamadou Diop</h1>
        <p>Omega Informatique Consulting - Gestion Intégrée</p>
    </div>

    <div class="card p-4 shadow-sm mb-4">
        <h4 class="text-center mb-4">Connexion</h4>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3"><label>Identifiant</label><input type="text" name="username" class="form-control" required></div>
            <div class="mb-3"><label>Mot de passe</label><input type="password" name="password" class="form-control" required></div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>

    <div class="card p-3 shadow-sm" style="width: 400px;">
        <small class="text-muted text-center mb-2">Comptes de test (Mot de passe: 123)</small>
        <table class="table table-sm table-bordered mb-0">
            <thead><tr><th>Rôle</th><th>Identifiant</th></tr></thead>
            <tbody>
                <tr><td>Admin</td><td>admin (admin123)</td></tr>
                <tr><td>Secrétariat</td><td>secretariat</td></tr>
                <tr><td>Sage-femme</td><td>sagefemme1</td></tr>
                <tr><td>Caissier</td><td>caissier1</td></tr>
            </tbody>
        </table>
    </div>
</body>
</html>
