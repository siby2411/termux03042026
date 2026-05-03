<?php
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE (username=? OR email=?) AND is_active=1");
    $stmt->execute([$_POST['username'], $_POST['username']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        redirect('/dashboard.php');
    }
    $error = "Identifiants incorrects";
}
?>
<!DOCTYPE html><html><head><title>Connexion</title><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins&display=swap" rel="stylesheet"><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gradient-to-r from-purple-600 to-pink-600 min-h-screen flex items-center justify-center"><div class="bg-white rounded-2xl p-8 w-full max-w-md"><div class="text-center mb-6"><i class="fas fa-spa text-5xl text-yellow-600"></i><h1 class="text-3xl font-playfair font-bold">Omega Cosmetique</h1></div>
<?php if(isset($error)): ?><div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php echo $error; ?></div><?php endif; ?>
<form method="POST"><div class="mb-4"><input type="text" name="username" placeholder="Nom d'utilisateur" required class="w-full px-4 py-3 border rounded-lg"></div><div class="mb-6"><input type="password" name="password" placeholder="Mot de passe" required class="w-full px-4 py-3 border rounded-lg"></div><button type="submit" class="w-full btn-luxury py-3">Se connecter</button></form></div></body></html>
