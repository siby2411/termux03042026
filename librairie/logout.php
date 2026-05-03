<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/config.php';

// Si la confirmation est demandée
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Enregistrer l'activité
    logActivity('logout', 'Déconnexion de ' . ($_SESSION['user_name'] ?? $_SESSION['user_username'] ?? 'Utilisateur'));
    
    // Détruire la session
    session_unset();
    session_destroy();
    
    // Redirection avec message
    header('Location: login.php?message=deconnexion_reussie');
    exit;
}

// Afficher une page de confirmation
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .confirm-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
            padding: 30px;
            max-width: 400px;
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .confirm-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .btn-logout {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
            padding: 10px 30px;
            font-weight: bold;
        }
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-cancel {
            background: #6c757d;
            border: none;
            color: white;
            padding: 10px 30px;
            font-weight: bold;
            margin-left: 10px;
        }
        .btn-cancel:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="confirm-card">
        <div class="confirm-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h3>Déconnexion</h3>
        <p class="text-muted">Êtes-vous sûr de vouloir vous déconnecter ?</p>
        <p><strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_username'] ?? 'Utilisateur'); ?></strong></p>
        
        <div class="mt-4">
            <a href="logout.php?confirm=yes" class="btn btn-logout">
                <i class="fas fa-check"></i> Oui, me déconnecter
            </a>
            <a href="javascript:history.back()" class="btn btn-cancel">
                <i class="fas fa-times"></i> Annuler
            </a>
        </div>
    </div>
</body>
</html>
<?php
exit;
?>
