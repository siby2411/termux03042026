<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SynthesePro UEMOA - Connexion</title>
    <style>
        body { font-family: Arial; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .login-box { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { color: #2c3e50; text-align: center; margin-bottom: 1.5rem; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background: #219a52; }
        .error { background: #e74c3c; color: white; padding: 12px; border-radius: 5px; margin: 15px 0; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 SynthesePro UEMOA</h2>
        <?php if (isset($message)): ?>
            <div class="error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" action="/synthesepro/?action=login">
            <input type="email" name="email" placeholder="admin@synthesepro.com" required autofocus>
            <input type="password" name="password" placeholder="password" required>
            <button type="submit">Connexion SYSCOHADA</button>
        </form>
        <p style="text-align:center;margin-top:15px;font-size:14px;color:#7f8c8d;">
            Test: admin@synthesepro.com / password
        </p>
    </div>
</body>
</html>

