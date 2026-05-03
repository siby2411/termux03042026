<?php

session_start();
include 'db_connect.php';

$error = "";

if (isset($_POST['user_input']) && isset($_POST['pass_input'])) {
    $user = trim($_POST['user_input']);
    $pass = trim($_POST['pass_input']);

    $user = $conn->real_escape_string($user);
    $pass = $conn->real_escape_string($pass);
    
    // Requête directe pour test
    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_name'] = $row['nom_complet'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Accès refusé. Vérifiez vos identifiants.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omega Auto | Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        .btn-primary { background: #2563eb; padding: 12px; font-weight: bold; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="card p-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">OMEGA AUTO</h2>
            <p class="text-muted small">Administration Système</p>
        </div>

        <?php if($error): ?>

            <div class="alert alert-danger py-2 small text-center"><?= $error ?></div>
        <?php endif; ?>


        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label small fw-bold">Utilisateur</label>
                <input type="text" name="user_input" class="form-control" placeholder="Entrez admin" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Mot de passe</label>
                <input type="password" name="pass_input" class="form-control" placeholder="Entrez admin123" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">SE CONNECTER</button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="index.php" class="text-decoration-none small text-muted">← Retour au site</a>
        </div>
    </div>
</body>
</html>
