<?php
session_start();
require_once '../../config/database.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: ../../redirect.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Chercher l'utilisateur
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_service'] = $user['service_id'];
            
            // Journaliser la connexion
            error_log("Connexion: {$user['role']} - {$user['prenom']} {$user['nom']}");
            
            header('Location: ../../redirect.php');
            exit();
        } else {
            $error = 'Identifiants incorrects';
        }
    } catch (Exception $e) {
        $error = 'Erreur de connexion: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Centre de Santé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h1 { font-size: 24px; margin: 10px 0 0; }
        .login-header i { font-size: 48px; }
        .login-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
        }
        .input-group { position: relative; }
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 1;
        }
        .form-control {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .users-list {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
        }
        .users-list h5 {
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .user-item {
            display: inline-block;
            background: white;
            padding: 5px 10px;
            margin: 3px;
            border-radius: 3px;
            border: 1px solid #ddd;
            font-size: 12px;
        }
        .user-item .role {
            color: #667eea;
            font-weight: bold;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-hospital"></i>
            <h1>Centre de Santé Mamadou Diop</h1>
            <p class="mb-0">Connexion à l'application</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" 
                               placeholder="Entrez votre nom d'utilisateur" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Mot de passe</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Entrez votre mot de passe" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>
            
            <div class="users-list">
                <h5><i class="fas fa-users"></i> Utilisateurs disponibles:</h5>
                <div>
                    <span class="user-item"><span class="role">Admin:</span> admin</span>
                    <span class="user-item"><span class="role">Caissier:</span> caissier1</span>
                    <span class="user-item"><span class="role">Médecin:</span> dr.fall, dr.diop, dr.ndiaye, dr.seck, dr.ba, dr.cisse, dr.gueye, dr.thiam, dr.kane, dr.sow</span>
                    <span class="user-item"><span class="role">Sage-femme:</span> sagefemme1</span>
                </div>
                <p class="text-muted mt-2 mb-0"><small>Mot de passe par défaut: 123456 (sauf admin/admin123)</small></p>
            </div>
        </div>
    </div>
</body>
</html>
