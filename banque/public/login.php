<?php
/**
 * PUBLIC/LOGIN.PHP
 * Formulaire de connexion sécurisé et stylisé.
 * Intègre la vérification du mot de passe haché et la désactivation de l'autocomplétion.
 */

session_start();
// La connexion à la BD est nécessaire pour l'authentification
require_once '../includes/db.php'; 

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Recherche de l'utilisateur par Email
    $sql = "SELECT PersonnelID, Nom, Prenoms, Email, MotDePasse, Role, Statut FROM PERSONNEL WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $personnel = $result->fetch_assoc();

        // **CORRECTION CRITIQUE** : Vérification du mot de passe haché
        if (password_verify($mot_de_passe, $personnel['MotDePasse'])) {
            
            // Connexion réussie : Définition des variables de session
            $_SESSION['user_id'] = $personnel['PersonnelID'];
            $_SESSION['user_name'] = $personnel['Prenoms'] . ' ' . $personnel['Nom'];
            $_SESSION['user_role'] = $personnel['Role'];
            
            // Redirection vers le tableau de bord
            header('Location: dashboard.php');
            exit;
        } else {
            $message = '<div class="alert alert-danger" role="alert">Identifiants incorrects ou mot de passe invalide.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger" role="alert">Identifiants incorrects ou utilisateur non trouvé.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Banque/Mutuelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            max-width: 400px;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.15);
            background-color: white;
            overflow: hidden;
        }
        .login-banner {
            height: 100px;
            /* Bleu Mutuelle & Vert - Un dégradé plus doux */
            background: linear-gradient(135deg, #007bff 0%, #28a745 100%); 
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.6rem;
            font-weight: 600;
            padding: 20px;
        }
        .login-form-content {
            padding: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="login-container">
                
                <div class="login-banner">
                    <i class="fas fa-university me-2"></i> BANQUE Mutuelle
                </div>

                <div class="login-form-content">
                    <h2 class="text-center mb-4 text-secondary">Accès Personnel</h2>
                    
                    <?php if ($message) { echo $message; } ?>

                    <form action="login.php" method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus autocomplete="off">
                        </div>
                        <div class="mb-4">
                            <label for="mot_de_passe" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required autocomplete="new-password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3"><i class="fas fa-sign-in-alt me-2"></i> Se Connecter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
