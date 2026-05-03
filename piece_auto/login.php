<?php
// 1. Activation des erreurs pour le débuggage sous Termux
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Gestion forcée des sessions (évite les erreurs de permission proot)
$session_dir = __DIR__ . '/sessions';
if (!is_dir($session_dir)) {
    mkdir($session_dir, 0777, true);
}
session_save_path($session_dir);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 3. Inclusions
include_once 'config/Database.php';
include_once 'config/globals.php';

$page_title = "Connexion à PieceAuto ERP";
$message = "";

// 4. Redirection si déjà connecté (utilisation de chemin relatif)
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// 5. Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = "<div class='alert alert-danger'>Veuillez remplir tous les champs.</div>";
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();

            if (!$db) {
                throw new Exception("Impossible de se connecter à la base de données.");
            }

            $query = "SELECT id_user, username, password_hash, role, is_active FROM USERS WHERE username = :username LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password_hash'])) {
                    if ($user['is_active']) {
                        // Succès : On stocke en session
                        $_SESSION['user_id'] = $user['id_user'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_role'] = $user['role'];
                        
                        // Redirection forcée en relatif pour éviter les bugs de port
                        header('Location: index.php');
                        exit;
                    } else {
                        $message = "<div class='alert alert-warning'>Compte inactif.</div>";
                    }
                } else {
                    $message = "<div class='alert alert-danger'>Mot de passe incorrect.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Utilisateur introuvable.</div>";
            }
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger'>Erreur système : " . $e->getMessage() . "</div>";
        }
    }
}

// Inclut le header
include 'includes/header.php';
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg" style="width: 100%; max-width: 400px;">
        <div class="card-header bg-dark text-white text-center">
            <h3><i class="fas fa-lock"></i> Connexion ERP</h3>
        </div>
        <div class="card-body">
            <?= $message ?>
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input type="text" class="form-control" name="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Se Connecter</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center text-muted">
            <small>Environnement : Termux Proot-Distro</small>
        </div>
    </div>
</div>

<?php 
// Pas de footer complexe pour le login
echo "</body></html>"; 
?>
