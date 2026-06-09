
<?php
session_start();

// si déjà connecté
if (isset($_SESSION['user'])) {
    header("Location: bilan.php");
    exit;
}

// login simple
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";

    if ($email === "admin@synthesepro" && $password === "password") {
        $_SESSION['user'] = $email;
        header("Location: bilan.php");
        exit;
    } else {
        $error = "Identifiants incorrects.";
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








