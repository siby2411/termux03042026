<?php
session_start();

/* Anti-redirection-infinie
   Si un utilisateur déjà connecté arrive ici → redirection vers dashboard */
if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) === "login.php") {
    header("Location: index.php");
    exit;
}

/* Connexion DB */
require_once __DIR__ . "/../includes/db.php";

$error = "";

/* Traitement formulaire */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    try {
        $stmt = $pdo->prepare("SELECT user_id, email, password_hash, role 
                               FROM USERS 
                               WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"]    = $user["user_id"];
            $_SESSION["email"]      = $user["email"];
            $_SESSION["role"]       = $user["role"];

            header("Location: index.php");
            exit;

        } else {
            $error = "Identifiants incorrects.";
        }

    } catch (PDOException $e) {
        $error = "Erreur DB : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion SYSCOHADA</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<style>
body {
    background: #f0f3f7;
}
.card-login {
    max-width: 420px;
    margin: 6% auto;
    padding: 35px;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0px 0px 35px rgba(0,0,0,0.15);
}
</style>
</head>

<body>

<div class="card-login">
    <h3 class="text-center mb-4">Connexion SYSCOHADA</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" 
                   class="form-control" 
                   required placeholder="admin@synthesepro.com">
        </div>

        <div class="mb-3">
            <label>Mot de Passe</label>
            <input type="password" name="password" 
                   class="form-control" 
                   required placeholder="••••••••">
        </div>

        <button class="btn btn-primary w-100 mt-2">Connexion</button>
    </form>

    <p class="text-muted text-center mt-3">
        <small>Phase test : admin@synthesepro.com / password</small><br>
        <small>comptable@synthesepro.com / password</small>
    </p>
</div>

</body>
</html>

