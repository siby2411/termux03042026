<?php
session_start();
require_once 'db_connect_ecole.php';
if (isset($_SESSION['role'])) { header("Location: index.php"); exit(); }
$conn = db_connect_ecole();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Priorité Admin (bypass test)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 0;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        header("Location: index.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, username, password, role, id_entite_associe, entite_type FROM utilisateurs_ecole WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['entite_id'] = $user['id_entite_associe'];
            $_SESSION['entite_type'] = $user['entite_type'];
            header("Location: " . ($user['role'] == 'professeur' ? 'prof_gestion_notes.php' : ($user['role'] == 'etudiant' ? 'etudiant_bulletin.php' : 'index.php')));
            exit();
        } else { $message = "Nom d'utilisateur ou mot de passe incorrect."; }
    } else { $message = "Nom d'utilisateur ou mot de passe incorrect."; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | Omega Informatique Consulting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; min-height: 100vh; display: flex; flex-direction: column; }
        .top-bar { 
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); 
            color: #fff; 
            padding: 80px 20px; 
            text-align: center; 
        }
        .login-wrapper {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: -100px;
        }
        .login-card { 
            background: white; 
            border-radius: 20px; 
            padding: 40px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.15); 
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="small text-uppercase mb-2" style="letter-spacing: 2px;">Omega Informatique Consulting</div>
        <h1 class="display-5">Espace École</h1>
        <p class="text-white-50">Gestion intégrée des établissements</p>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <?php if ($message): ?><p class="bg-danger text-white p-3 rounded mb-4 text-center"><?= $message ?></p><?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label font-bold">Utilisateur</label>
                    <input type="text" name="username" class="form-control" value="admin" required>
                </div>
                <div class="mb-3">
                    <label class="form-label font-bold">Mot de passe</label>
                    <input type="password" name="password" class="form-control" value="admin123" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold p-3 rounded hover:bg-blue-700 btn btn-primary">Se Connecter</button>
            </form>
        </div>
    </div>
</body>
</html>
