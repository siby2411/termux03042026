<?php
session_start();
require_once 'includes/auth.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Token Actuel</title>
    <meta http-equiv="refresh" content="5">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .patient-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; padding: 20px; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
        .btn-danger { background: #e74c3c; }
        .btn-secondary { background: #95a5a6; }
        .nav { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Visualisation du Token</h1>
        
        <div class="nav">
            <a href="modules/medecin/dashboard.php" class="btn">⬅️ Dashboard Médecin</a>
            <a href="reset_token.php" class="btn btn-danger">🗑️ Reset Patient</a>
            <a href="logout.php" class="btn btn-secondary">🚪 Déconnexion</a>
        </div>
        
        <div class="card">
            <h3>Session Utilisateur</h3>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p><strong>ID:</strong> <?= $_SESSION['user_id'] ?></p>
                <p><strong>Nom:</strong> <?= $_SESSION['user_prenom'] ?? '' ?> <?= $_SESSION['user_nom'] ?? '' ?></p>
                <p><strong>Rôle:</strong> <?= $_SESSION['user_role'] ?? '' ?></p>
                <p><strong>Connecté:</strong> ✅ Oui</p>
            <?php else: ?>
                <p style="color: red;">❌ Non connecté - <a href="login.php">Se connecter</a></p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3>Token Brut</h3>
            <pre><?= $_SESSION['user_token'] ?? 'Aucun token' ?></pre>
        </div>
        
        <div class="card">
            <h3>Token Décodé</h3>
            <pre><?php 
            if (isset($_SESSION['user_token'])) {
                $token = json_decode(base64_decode($_SESSION['user_token']), true);
                print_r($token);
            } else {
                echo "Aucun token";
            }
            ?></pre>
        </div>
        
        <div class="patient-card">
            <h3>🩺 Patient Actuel</h3>
            <?php 
            $patient = getPatientFromToken();
            if ($patient): 
            ?>
                <table style="width: 100%; color: white;">
                    <tr><td><strong>ID:</strong></td><td><?= $patient['id'] ?></td></tr>
                    <tr><td><strong>Nom complet:</strong></td><td><?= $patient['prenom'] ?> <?= $patient['nom'] ?></td></tr>
                    <tr><td><strong>Code patient:</strong></td><td><?= $patient['code'] ?></td></tr>
                    <tr><td><strong>Téléphone:</strong></td><td><?= $patient['telephone'] ?? 'N/A' ?></td></tr>
                </table>
            <?php else: ?>
                <p>Aucun patient chargé - Cliquez sur un patient dans le dashboard</p>
            <?php endif; ?>
        </div>
        
        <p style="color: #666; font-size: 12px; margin-top: 20px;">
            ⏱️ Auto-refresh toutes les 5 secondes
        </p>
    </div>
</body>
</html>
