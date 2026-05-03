<?php
require_once __DIR__.'/core/Auth.php';
if(!defined('APP_VERSION')) define('APP_VERSION', '2.0.3');

if(session_status()===PHP_SESSION_NONE) session_start();

if(!empty($_SESSION['user_id'])){
    header('Location: /index.php'); exit;
}

$err = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(Auth::login(trim($_POST['login']??''), $_POST['password']??'')){
        header('Location: /index.php'); exit;
    }
    $err = 'Accès refusé. Identifiants incorrects.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Omega Pharma — Connexion</title>
    <style>
        :root { --primary: #1a7f5a; --secondary: #0d4f38; --accent: #ffd700; }
        *{box-sizing:border-box; margin:0; padding:0; font-family:'Segoe UI',sans-serif;}
        
        body {
            background: #e9ecef;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
        }

        .login-card {
            width: 100%; max-width: 420px;
            background: #fff; border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15); overflow: hidden;
            border: 1px solid #ddd;
        }

        .omega-header {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            padding: 25px; text-align: center; color: #fff;
            border-bottom: 4px solid var(--accent);
        }
        
        .logo-box {
            background: rgba(255,255,255,0.1);
            width: 70px; height: 70px; margin: 0 auto 15px;
            border-radius: 15px; display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--accent);
            font-size: 2.5rem; color: var(--accent);
        }

        .omega-header h2 { font-size: 0.95rem; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 5px; color: var(--accent); }
        .omega-header p { font-size: 0.7rem; opacity: 0.9; font-weight: 400; line-height: 1.4; }
        .copyright-name { display: block; margin-top: 5px; font-style: italic; color: #fff; font-size: 0.65rem; opacity: 0.8; }

        .form-body { padding: 35px; }
        .form-title { text-align: center; margin-bottom: 25px; }
        .form-title h1 { font-size: 1.5rem; color: var(--secondary); margin-bottom: 5px; }

        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 0.85rem; color: #666; margin-bottom: 8px; font-weight: 600; }
        .input-group input { 
            width: 100%; padding: 14px; border: 1.5px solid #eee; border-radius: 10px;
            background: #f8f9fa; font-size: 1rem; transition: 0.3s;
        }
        .input-group input:focus { border-color: var(--primary); background: #fff; outline: none; box-shadow: 0 0 0 4px rgba(26,127,90,0.1); }

        .btn-login {
            width: 100%; padding: 15px; background: var(--primary); color: #fff;
            border: none; border-radius: 12px; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: 0.3s;
        }
        .btn-login:hover { background: var(--secondary); transform: translateY(-1px); }

        .error-msg { background: #fff5f5; color: #c0392b; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; border: 1px solid #feb2b2; }
        
        .footer-text { text-align: center; padding: 20px; color: #999; font-size: 0.7rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="omega-header">
            <div class="logo-box">Ω</div>
            <h2>OMEGA INFORMATIQUE</h2>
            <p>
                CONSULTING & SOLUTIONS DIGITALES<br>
                <span class="copyright-name">Copyright © Mr Mohamed Siby - Consultant en informatique</span>
            </p>
        </div>

        <div class="form-body">
            <div class="form-title">
                <h1>PharmaSen v2.0</h1>
                <p style="font-size:0.8rem; color:#888;">Gestion d'Officine — Sénégal</p>
            </div>

            <?php if($err): ?>
                <div class="error-msg">⚠️ <?=$err?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Identifiant utilisateur</label>
                    <input type="text" name="login" required placeholder="Ex: amadou" autofocus>
                </div>
                <div class="input-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn-login">S'AUTHENTIFIER</button>
            </form>
        </div>

        <div class="footer-text">
            Propulsé par <strong>OMEGA CONSULTING</strong><br>
            Dakar, Sénégal &copy; 2026
        </div>
    </div>
</body>
</html>
