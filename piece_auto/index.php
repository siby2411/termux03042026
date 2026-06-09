<?php
session_start();
// Vérification stricte : si pas de session, on va au login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="container mt-5">
    <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    <p>Vous êtes connecté en tant que : <strong><?php echo $_SESSION['user_role']; ?></strong></p>
    <a href="logout.php" class="btn btn-danger">Déconnexion</a>
</body>
</html>
