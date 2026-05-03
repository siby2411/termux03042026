<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation inscription - OMEGA Transport</title>
    <style>
        .confirmation-container { text-align: center; padding: 100px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card-confirm { background: white; max-width: 600px; margin: auto; padding: 40px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .btn-dashboard { background: #003366; color: white; padding: 12px 30px; border-radius: 30px; display: inline-block; margin-top: 20px; text-decoration: none; }
        .credentials { background: #f0f0f0; padding: 15px; border-radius: 10px; margin: 20px 0; font-family: monospace; }
    </style>
</head>
<body>
<div class="confirmation-container">
    <div class="card-confirm">
        <i class="fas fa-check-circle" style="font-size: 80px; color: #4CAF50;"></i>
        <h2>Inscription réussie !</h2>
        <p>Votre demande a bien été enregistrée. Un email/SMS de confirmation vous a été envoyé.</p>
        
        <?php if(isset($_SESSION['temp_login']) && isset($_SESSION['temp_password'])): ?>
        <div class="credentials">
            <strong>Vos identifiants de connexion :</strong><br>
            Login: <strong><?php echo $_SESSION['temp_login']; ?></strong><br>
            Mot de passe: <strong><?php echo $_SESSION['temp_password']; ?></strong><br>
            <small class="text-muted">⚠️ Conservez ces informations. Vous pourrez vous connecter après validation de votre dossier.</small>
        </div>
        <?php unset($_SESSION['temp_login'], $_SESSION['temp_password']); ?>
        <?php endif; ?>
        
        <a href="/transport/login_parent.php" class="btn-dashboard">Se connecter</a>
    </div>
</div>
<script src="https://kit.fontawesome.com/yourcode.js" crossorigin="anonymous"></script>
</body>
</html>
