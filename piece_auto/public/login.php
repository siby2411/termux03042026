<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. On s'assure qu'aucune session n'est active avant de configurer le path
if (session_status() !== PHP_SESSION_NONE) {
    session_write_close();
}

// 2. Configuration du dossier de session pour Termux
$session_path = '/tmp/php_sessions';
if (!is_dir($session_path)) { 
    @mkdir($session_path, 0777, true); 
}
session_save_path($session_path);

// 3. Démarrage propre
session_start();

// 4. Inclusion de la base de données
$db_file = '/root/shared/htdocs/apachewsl2026/piece_auto/config/Database.php';
if (file_exists($db_file)) {
    require_once $db_file;
} else {
    die("Fichier Database.php introuvable.");
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $db->prepare("SELECT * FROM UTILISATEURS WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role']; 
            $_SESSION['full_name'] = $user['prenom'] . " " . $user['nom'];
            
            header("Location: modules/tableau_de_bord.php");
            exit();
        } else {
            $error = "Identifiants incorrects.";
        }
    } catch (Exception $e) {
        $error = "Erreur SQL : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - OMEGA PIECES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 380px; border: none; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="card login-card shadow-lg p-4">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">OMEGA PIECES</h2>
            <p class="text-muted small">Connectez-vous pour gérer votre stock</p>
        </div>
        <?php if($error): ?>
            <div class="alert alert-danger py-2 small"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Utilisateur" required autofocus>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">SE CONNECTER</button>
        </form>
    </div>
</body>
</html>
