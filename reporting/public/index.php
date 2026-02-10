<?php
session_start();
require_once __DIR__ . '/../app/Models/Db.php';

$error = '';

if(php_sapi_name() === 'cli'){
    echo "Mode CLI détecté.\n";
    $pdo = \App\Models\Db::getInstance()->getConnection();
    echo "Connexion DB OK ✅\n";
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    $pdo = \App\Models\Db::getInstance()->getConnection();

    $stmt = $pdo->prepare("SELECT * FROM USERS WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($pass, $user['password_hash'])){
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php"); exit;
    } else {
        $error = "Identifiants incorrects : ";
        $error .= $user ? "Mot de passe incorrect" : "Utilisateur inexistant";
    }
}
?>
<h2>Connexion Reporting</h2>
<?php if($error) echo "<p style='color:red'>$error</p>"; ?>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Mot de passe: <input type="password" name="password" required><br>
    <button type="submit">Se connecter</button>
</form>
