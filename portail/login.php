<?php
session_start();
require_once 'includes/db.php';

// Si déjà connecté, rediriger vers index
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$type = $_GET['type'] ?? 'client';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $type = $_POST['type'] ?? 'client';

    $pdo = getPDO();
    
    if ($type === 'client') {
        $stmt = $pdo->prepare("SELECT id, nom, prenom, email, mot_de_passe FROM clients WHERE email = ? AND actif = 1");
    } else {
        $stmt = $pdo->prepare("SELECT id, nom, email, mot_de_passe FROM fournisseurs WHERE email = ? AND actif = 1");
    }
    
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $type;
        $_SESSION['user_name'] = $type === 'client' ? ($user['prenom'] . ' ' . $user['nom']) : $user['nom'];
        $_SESSION['user_email'] = $user['email'];
        
        // Mettre à jour dernière connexion
        $updateSql = $type === 'client' 
            ? "UPDATE clients SET derniere_connexion = NOW() WHERE id = ?" 
            : "UPDATE fournisseurs SET derniere_connexion = NOW() WHERE id = ?";
        $pdo->prepare($updateSql)->execute([$user['id']]);
        
        header('Location: index.php');
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Connexion - Portail E-Commerce</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><style>
body{background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;margin:0;padding:20px;}
.login-card{background:rgba(255,255,255,0.95);border-radius:20px;box-shadow:0 20px 40px rgba(0,0,0,0.3);overflow:hidden;max-width:450px;width:100%;}
.login-header{background:linear-gradient(135deg,#ff6b6b,#ff8c8c);padding:30px;text-align:center;color:white;}
.login-header i{font-size:3rem;margin-bottom:10px;}
.login-header h3{margin:0;font-weight:700;}
.login-body{padding:30px;}
.form-control{border-radius:10px;padding:12px;}
.btn-login{background:linear-gradient(135deg,#ff6b6b,#ff8c8c);border:none;border-radius:10px;padding:12px;font-weight:600;transition:all 0.3s;width:100%;color:white;}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(255,107,107,0.4);}
</style></head>
<body>
<div class="login-card">
    <div class="login-header">
        <i class="fas fa-store"></i>
        <i class="fas fa-shopping-cart"></i>
        <h3>Portail E-Commerce</h3>
        <p>Oméga informatique CONSULTING</p>
    </div>
    <div class="login-body">
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Type de compte</label>
                <select name="type" class="form-control">
                    <option value="client" <?= $type=='client' ? 'selected' : '' ?>>Client</option>
                    <option value="fournisseur" <?= $type=='fournisseur' ? 'selected' : '' ?>>Fournisseur</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>
        <div class="text-center mt-3 small text-muted">
            <i class="fas fa-user-tie"></i> Mohamed Siby - Consultant en Informatique
        </div>
    </div>
</div>
</body>
</html>
