<?php
require_once 'config/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username=? AND is_active=1");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id']; $_SESSION['user_name'] = $user['full_name']; $_SESSION['user_role'] = $user['role']; $_SESSION['logged_in'] = true;
        header("Location: /dashboard.php"); exit();
    } else $error = "Identifiants incorrects";
}
?>
<!DOCTYPE html><html><head><title>Connexion Scooter</title><link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></head>
<body class="bg-gradient-to-r from-red-600 to-orange-600 min-h-screen flex items-center justify-center"><div class="bg-white rounded-2xl p-8 w-full max-w-md"><div class="text-center mb-6"><i class="fas fa-motorcycle text-5xl text-red-600"></i><h1 class="text-3xl font-bold">Omega Scooter</h1></div>
<?php if(isset($error)): ?><div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php echo $error; ?></div><?php endif; ?>
<form method="POST"><div class="mb-4"><input type="text" name="username" placeholder="Nom d'utilisateur" required class="w-full px-4 py-3 border rounded-lg"></div><div class="mb-6"><input type="password" name="password" placeholder="Mot de passe" required class="w-full px-4 py-3 border rounded-lg"></div><button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700">Se connecter</button></form></div></body></html>
