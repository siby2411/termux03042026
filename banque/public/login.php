<?php
session_start();
require_once '../includes/db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Priorité Admin (bypass pour tests)
    if ($email === 'admin@omega.sn' && $mot_de_passe === 'admin123') {
        $_SESSION['user_id'] = 0;
        $_SESSION['user_name'] = 'Administrateur Omega';
        $_SESSION['user_role'] = 'admin';
        header('Location: dashboard.php');
        exit;
    }

    $sql = "SELECT PersonnelID, Nom, Prenoms, Email, MotDePasse, Role, Statut FROM PERSONNEL WHERE Email = '$email'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows == 1) {
        $personnel = $result->fetch_assoc();
        if (password_verify($mot_de_passe, $personnel['MotDePasse'])) {
            $_SESSION['user_id'] = $personnel['PersonnelID'];
            $_SESSION['user_name'] = $personnel['Prenoms'] . ' ' . $personnel['Nom'];
            $_SESSION['user_role'] = $personnel['Role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $message = '<div class="alert alert-danger">Identifiants incorrects.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Utilisateur non trouvé.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | Banque Mutuelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; min-height: 100vh; display: flex; flex-direction: column; }
        .top-bar { 
            background: linear-gradient(135deg, #002d72 0%, #0045a5 100%); 
            color: #d4af37; 
            padding: 80px 20px; 
            text-align: center; 
        }
        .login-wrapper {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: -100px;
        }
        .login-card { 
            background: white; 
            border-radius: 20px; 
            padding: 40px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.15); 
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="small text-uppercase mb-2" style="letter-spacing: 2px;">Omega Informatique Consulting</div>
        <h1 class="display-5 text-white">BANQUE MUTUELLE</h1>
        <p>Accès Privé - Espace Personnel</p>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <?php if ($message) echo $message; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email professionnel</label>
                    <input type="email" name="email" class="form-control" value="admin@omega.sn" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="mot_de_passe" class="form-control" value="admin123" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3 py-2" style="background:#002d72;">SE CONNECTER</button>
            </form>
        </div>
    </div>
</body>
</html>
