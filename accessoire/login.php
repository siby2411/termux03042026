<?php
// /var/www/piece_auto/login.php
include_once 'config/Database.php';
include_once 'config/globals.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Connexion à PieceAuto ERP";
$message = "";

if (isset($_SESSION['user_id'])) {
    // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
    header('Location: ' . $GLOBALS['app_root'] . '/index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = "<div class='alert alert-danger'>Veuillez saisir votre nom d'utilisateur et votre mot de passe.</div>";
    } else {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id_user, username, password_hash, role, is_active FROM USERS WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // CORRECTION CRITIQUE: Utiliser password_verify() pour les hachages PHP (bcrypt)
            if (password_verify($password, $user['password_hash']) && $user['is_active']) {
                // Succès de la connexion
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                header('Location: ' . $GLOBALS['app_root'] . '/index.php');
                exit;
            } elseif (!$user['is_active']) {
                 $message = "<div class='alert alert-warning'>Votre compte est inactif. Veuillez contacter l'administrateur.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Nom d'utilisateur ou mot de passe incorrect.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Nom d'utilisateur ou mot de passe incorrect.</div>";
        }
    }
}

// Inclut le header sans la navigation latérale
include 'includes/header.php';
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg" style="width: 100%; max-width: 400px;">
        <div class="card-header bg-dark text-white text-center">
            <h3><i class="fas fa-lock"></i> Connexion ERP PieceAuto</h3>
        </div>
        <div class="card-body">
            <?= $message ?>
            <form method="POST" action="<?= $GLOBALS['app_root'] ?>/login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Se Connecter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// On n'inclut pas le footer.php ici pour garder la mise en page centrée du login simple.
?>
