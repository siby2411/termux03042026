<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    if ($email && $pass) {
        $pdo = getPDO();
        $st  = $pdo->prepare("SELECT * FROM utilisateurs WHERE email=?");
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            loginUser($u['id'], $u['nom'], $u['role']);
            header('Location: index.php'); exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Connexion Admin – OMEGA Charcuterie</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Raleway',sans-serif;min-height:100vh;
  background:linear-gradient(135deg,#0d0d0d 0%,#1a0505 50%,#0d0d0d 100%);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  position:relative;overflow:hidden}
body::before{content:'🥩';position:fixed;font-size:50vw;opacity:.02;
  top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;z-index:0}
.header-band{position:fixed;top:0;left:0;right:0;
  background:linear-gradient(90deg,#922b21,#c0392b,#d4ac0d,#c0392b,#922b21);
  background-size:300%;animation:grad 6s ease infinite;
  text-align:center;padding:10px;z-index:100;font-family:'Playfair Display',serif;
  font-size:.9rem;color:#fff;letter-spacing:3px;text-transform:uppercase}
@keyframes grad{0%{background-position:0%}50%{background-position:100%}100%{background-position:0%}}
.login-box{
  background:rgba(255,255,255,.04);backdrop-filter:blur(20px);
  border:1px solid rgba(255,255,255,.1);border-radius:24px;
  padding:50px 45px;width:100%;max-width:420px;position:relative;z-index:1;
  box-shadow:0 30px 60px rgba(0,0,0,.5)}
.login-box::before{content:'';position:absolute;inset:0;border-radius:24px;
  background:linear-gradient(135deg,rgba(192,57,43,.1),rgba(212,172,13,.05));pointer-events:none}
.logo-area{text-align:center;margin-bottom:35px}
.logo-area .icon{font-size:3rem;display:block;margin-bottom:10px}
.logo-area h1{font-family:'Playfair Display',serif;color:#fff;font-size:1.6rem;margin-bottom:5px}
.logo-area p{color:#888;font-size:.8rem;letter-spacing:2px;text-transform:uppercase}
.form-group{margin-bottom:20px}
.form-group label{display:block;color:#ccc;font-size:.85rem;font-weight:600;
  margin-bottom:8px;letter-spacing:.5px}
.form-group .input-wrap{position:relative}
.form-group .input-wrap i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#888;font-size:.9rem}
.form-group input{width:100%;padding:13px 15px 13px 42px;
  background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
  border-radius:12px;color:#fff;font-size:.95rem;font-family:'Raleway',sans-serif;
  outline:none;transition:.3s}
.form-group input:focus{border-color:#c0392b;background:rgba(192,57,43,.08);
  box-shadow:0 0 0 3px rgba(192,57,43,.15)}
.form-group input::placeholder{color:#555}
.alert-error{background:rgba(192,57,43,.2);border:1px solid rgba(192,57,43,.4);
  color:#e74c3c;padding:12px 15px;border-radius:10px;font-size:.85rem;margin-bottom:20px;
  display:flex;align-items:center;gap:10px}
.btn-login{width:100%;padding:14px;
  background:linear-gradient(135deg,#c0392b,#922b21);
  border:none;border-radius:12px;color:#fff;font-size:1rem;font-weight:700;
  font-family:'Raleway',sans-serif;cursor:pointer;transition:.3s;letter-spacing:1px;
  text-transform:uppercase}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(192,57,43,.4)}
.login-footer{text-align:center;margin-top:25px}
.login-footer a{color:#d4ac0d;text-decoration:none;font-size:.85rem;
  transition:.3s;display:inline-flex;align-items:center;gap:8px}
.login-footer a:hover{color:#f1c40f}
.demo-info{background:rgba(212,172,13,.1);border:1px solid rgba(212,172,13,.2);
  border-radius:10px;padding:12px 15px;margin-top:20px;font-size:.78rem;color:#d4ac0d;text-align:center}
</style>
</head>
<body>
<div class="header-band">🏆 OMEGA INFORMATIQUE CONSULTING — GESTION CHARCUTERIE 🏆</div>

<div class="login-box">
  <div class="logo-area">
    <span class="icon">🔐</span>
    <h1>Administration</h1>
    <p>Espace sécurisé – Connexion requise</p>
  </div>

  <?php if($error): ?>
  <div class="alert-error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Adresse Email</label>
      <div class="input-wrap">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" placeholder="admin@omega.com" required
          value="<?= htmlspecialchars($_POST['email']??'') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Mot de passe</label>
      <div class="input-wrap">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
    </div>
    <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Se connecter</button>
  </form>

  <div class="demo-info">
    <strong>Compte démo :</strong><br>
    Email: admin@omega.com &nbsp;|&nbsp; MDP: password
  </div>

  <div class="login-footer">
    <a href="../index.php"><i class="fas fa-arrow-left"></i> Retour au site</a>
  </div>
</div>
</body>
</html>
