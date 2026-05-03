<?php
// login.php
session_start();
$page_title = "Connexion Sécurisée";
include_once __DIR__ . '/config/db.php';
// Nous n'incluons pas le header standard pour éviter les menus non connectés

$database = new Database();
$db = $database->getConnection();
$message = '';

// Si l'utilisateur est déjà connecté, le rediriger vers l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Utilisation d'une table "Utilisateurs" (ou d'un utilisateur en dur pour le test)
    if (!empty($username) && !empty($password)) {
        
        $query = "SELECT UserID, MotDePasse FROM Users WHERE NomUtilisateur = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verification du mot de passe
        if ($user && password_verify($password, $user['MotDePasse'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $username;
            
            // Redirection sécurisée
          


        // Ajoutez le rôle à la session (si vous voulez l'utiliser dans le header/dashboard)
        // Note: Vous devrez ajouter le champ 'Role' dans la requête SELECT
        // $query = "SELECT UserID, MotDePasse, Role FROM Users WHERE NomUtilisateur = :username";
        // $_SESSION['role'] = $user['Role']; 
        
        // Redirection vers le dashboard, en utilisant le chemin d'accès absolu
        header('Location: /gestion_previsionnelle/dashboard_pilote.php');
        exit;










        } else {
            $message = "<div class='alert alert-danger'>Nom d'utilisateur ou mot de passe incorrect.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Veuillez entrer les identifiants.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .login-container { max-width: 400px; margin-top: 10vh; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 login-container">
            <div class="card shadow-lg border-0">
                <div class="card-header text-center bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-lock me-2"></i> Gestion Prévisionnelle</h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-center text-muted">Veuillez vous identifier pour accéder au tableau de bord.</p>
                    <?= $message ?>
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" name="username" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt me-2"></i> Connexion</button>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <small class="text-muted">&copy; 2024 Gestion Prévisionnelle</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
