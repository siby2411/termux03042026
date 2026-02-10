 

<?php
session_start();
require_once __DIR__ . '/../config/config.php'; // inclusion config.php

// Message d'erreur par défaut
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            // Respect de la casse : table USERS en majuscule
            $stmt = $pdo->prepare("SELECT * FROM `USERS` WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirection selon rôle
                if ($user['role'] === 'ADMIN') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['role'] === 'COMPTABLE') {
                    header("Location: comptable_dashboard.php");
                } else {
                    header("Location: lecteur_dashboard.php");
                }
                exit();
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            die("Erreur SQL : " . $e->getMessage());
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - SynthesePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fa;
            height: 100vh;
        }
        .login-card {
            max-width: 420px;
            margin: auto;
            margin-top: 6%;
            padding: 30px;
            border-radius: 16px;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="login-card text-center">
        <img src="omega.jpg" class="logo" alt="Logo Finance">
        <h4 class="mb-3">SynthèsePro <br><small>Plateforme Financière SYSCOHADA</small></h4>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-1"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
                <label>Email</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" name="password" placeholder="Mot de passe" required>
                <label>Mot de passe</label>
            </div>

            <button class="btn btn-primary w-100" type="submit">Connexion</button>
        </form>

        <p class="mt-4 text-muted" style="font-size:14px;">
            <strong>username :</strong> admin@synthesepro<br>
            <strong>password :</strong> password
        </p>
    </div>
</body>
</html>













 
