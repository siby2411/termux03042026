<?php
// /var/www/piece_auto/public/login.php - Version finale pour la connexion

// Démarrer la session en premier lieu
session_start();

// Si l'utilisateur est déjà connecté, le rediriger vers l'index
if (isset($_SESSION['user_id'])) {
    header('Location: /piece_auto/public/index.php');
    exit;
}

// Inclure la classe de base de données (le chemin est critique)
include '../config/Database.php';

$database = new Database();
$db = $database->getConnection(); 
$login_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username_input = $_POST['username'] ?? ''; 
    $password_input = $_POST['password'] ?? '';

    // 1. REQUÊTE SQL : Récupération de l'utilisateur par le Nom d'utilisateur
    // Utilise la table USERS et les champs id_user, username, password_hash, role
    $query = "SELECT id_user, username, password_hash, role FROM USERS WHERE username = :username AND is_active = 1 LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':username' => $username_input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. VÉRIFICATION DU MOT DE PASSE HACHÉ
    if ($user && password_verify($password_input, $user['password_hash'])) {
        // Connexion réussie : Initialisation de la session
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirection vers le tableau de bord
        header('Location: /piece_auto/public/index.php');
        exit;
    } else {
        // Échec de connexion (utilisateur non trouvé ou mot de passe incorrect)
        $login_message = "<div class='alert alert-danger'>Nom d'utilisateur ou mot de passe incorrect.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion Pièces Auto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 400px; margin-top: 100px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 login-container">
                <div class="card p-4 shadow-lg">
                    <h2 class="text-center mb-4"><i class="fas fa-lock"></i> Connexion</h2>
                    <?= $login_message ?>
                    
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Se Connecter</button>
                        </div>
                    </form>
                    <p class="mt-3 text-center text-muted">Système de Gestion des Pièces Auto</p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
