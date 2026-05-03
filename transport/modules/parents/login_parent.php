<?php
session_start();
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($_POST['login'] === 'parent.test' && $_POST['password'] === 'password') {
        $_SESSION['user_role'] = 'parent';
        $_SESSION['user_name'] = 'Parent Test';
        header('Location: dashboard_parent_complet.php');
        exit();
    } else { $error = 'Identifiants incorrects'; }
}
include __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-5" style="max-width: 450px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center"><h3>Espace Parent</h3></div>
        <div class="card-body">
            <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <form method="POST">
                <input type="text" name="login" class="form-control mb-3" placeholder="Login" required>
                <input type="password" name="password" class="form-control mb-3" placeholder="Mot de passe" required>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
            <div class="text-center mt-3"><a href="inscription_parent.php">Nouveau ? Inscrivez votre enfant</a></div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
