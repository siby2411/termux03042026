<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - OMEGA Hôtel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width:400px">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="text-center"><i class="fas fa-hotel me-2"></i>OMEGA Hôtel</h3>
                <hr>
                <div class="alert alert-info">
                    <strong>Connexion démo :</strong><br>
                    Utilisateur: admin<br>
                    Mot de passe: admin123
                </div>
                <form method="post" action="index.php">
                    <div class="mb-3">
                        <label>Nom d'utilisateur</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php" class="btn btn-link">Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
