<?php
session_start();
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($_POST['login'] === 'secretaire' && $_POST['password'] === 'omega2025') {
        $_SESSION['user_role'] = 'secretaire';
        $_SESSION['user_name'] = 'Secrétaire';
        header('Location: modules/paiements/gestion_paiement.php');
        exit();
    } else { $error = 'Identifiants incorrects'; }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Espace Secrétaire</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body style="background: linear-gradient(135deg, #003366 0%, #006699 100%); min-height: 100vh; display: flex; align-items: center;">
<div class="container" style="max-width: 450px;"><div class="card shadow"><div class="card-header bg-dark text-white text-center"><i class="fas fa-user-tie fa-3x"></i><h3>Espace Secrétaire</h3></div>
<div class="card-body p-4"><?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<form method="POST"><div class="mb-3"><label>Login</label><input type="text" name="login" class="form-control" required></div>
<div class="mb-3"><label>Mot de passe</label><input type="password" name="password" class="form-control" required></div>
<button type="submit" class="btn btn-dark w-100">Se connecter</button></form>
<div class="text-center mt-3 text-muted"><small>Identifiants: secretaire / omega2025</small></div>
</div></div></div></body></html>
