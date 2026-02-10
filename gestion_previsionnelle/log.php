<?php
// /login.php
$page_title = "Connexion au Système";

// NOTE: Le login.php est généralement le premier fichier, donc il inclut peu de dépendances.
// Nous allons inclure uniquement les balises HTML de base et Bootstrap.

$message = ''; // Message d'erreur ou de succès après tentative de connexion

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Logique de vérification des identifiants (Simulée)
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Remplacer par une vraie vérification de base de données !
    if ($username === 'manager' && $password === '1234') {
        // Redirection vers le tableau de bord en cas de succès
        header("Location: dashboard_pilote.php");
        exit;
    } else {
        $message = "<div class='alert alert-danger mt-3'>Nom d'utilisateur ou mot de passe incorrect.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* Centrage vertical de la page de connexion */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa; /* Couleur de fond légère */
        }
        .login-card {
            max-width: 450px;
            padding: 30px;
        }
        .omega-logo {
            width: 100%; /* S'assure que l'image est responsive */
            height: auto;
            max-height: 200px; /* Limite la hauteur de l'image */
            object-fit: contain; /* Assure que l'image est visible dans le cadre sans coupure */
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="card login-card shadow-lg border-0">
    <div class="card-body">
        
        <img src="assets/images/omega.jpg" alt="Logo Omega" class="omega-logo d-block mx-auto">
        
        <h2 class="text-center mb-4"><i class="fas fa-lock me-2"></i> Connexion</h2>

        <?= $message ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Se Connecter</button>
            </div>
        </form>
        
        <p class="text-center text-muted mt-3"><small>Système de Gestion Prévisionnelle</small></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
