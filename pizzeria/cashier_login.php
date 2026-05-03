<?php
session_start();
require_once 'config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        // Connexion directe à la base de données
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT * FROM cashiers WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $cashier = $stmt->fetch();
            
            if ($cashier && password_verify($password, $cashier['password'])) {
                $session_token = bin2hex(random_bytes(32));
                $_SESSION['cashier_id'] = $cashier['id'];
                $_SESSION['cashier_name'] = $cashier['full_name'];
                $_SESSION['cashier_username'] = $cashier['username'];
                $_SESSION['session_token'] = $session_token;
                
                // Enregistrer la session
                $stmt2 = $pdo->prepare("INSERT INTO cashier_sessions (cashier_id, session_token, ip_address) VALUES (?, ?, ?)");
                $stmt2->execute([$cashier['id'], $session_token, $_SERVER['REMOTE_ADDR']]);
                
                header('Location: pos.php');
                exit;
            } else {
                $error = "Identifiants incorrects";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Caissier - Pizzeria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .login-logo { font-size: 3rem; text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="login-card">
                    <div class="login-logo"><i class="fas fa-pizza-slice"></i></div>
                    <h3 class="text-center mb-4">Espace Caissier</h3>
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nom d'utilisateur</label>
                            <input type="text" name="username" class="form-control" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
