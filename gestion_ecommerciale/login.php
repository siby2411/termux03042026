<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'db_connect.php';

if (isset($_SESSION['id_vendeur'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = db_connect();
    $stmt = $conn->prepare("SELECT id_vendeur, nom, mot_de_passe FROM vendeurs WHERE email = ? AND statut = 'actif'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Vérification du mot de passe haché
        if (password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['id_vendeur'] = $user['id_vendeur'];
            $_SESSION['nom_vendeur'] = $user['nom'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Utilisateur non trouvé ou inactif.";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Omega Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; display: flex; align-items: center; }
        .card { border: none; border-radius: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-lg p-4">
                    <h2 class="text-center text-primary mb-4">Omega Market</h2>
                    <?php if($error): ?>
                        <div class="alert alert-danger p-2 small"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="momo@omega.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Se connecter</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="index.php" class="text-decoration-none small text-muted">Retour à l'accueil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
