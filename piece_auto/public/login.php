<?php
// /var/www/piece_auto/public/login.php
// Nous incluons le header en premier pour gérer les sessions et le chemin 'app_root'

// CORRECTION: Utiliser '../' pour inclure les dépendances depuis /public
include '../config/Database.php';
include '../includes/header.php'; 

// Le header.php gère maintenant la session_start()

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $query = "SELECT id_utilisateur, nom_utilisateur, mot_de_passe, role FROM UTILISATEURS WHERE nom_utilisateur = :username LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        // Authentification réussie
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['username'] = $user['nom_utilisateur'];
        $_SESSION['user_role'] = $user['role'];
        
        // Redirection vers la page d'accueil après le login
        header('Location: ' . $GLOBALS['app_root'] . '/index.php');
        exit;
    } else {
        $message = "Nom d'utilisateur ou mot de passe invalide.";
    }
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-lg" style="width: 100%; max-width: 400px;">
        <div class="card-header bg-dark text-white text-center">
            <h3><i class="fas fa-lock"></i> Connexion PieceAuto ERP</h3>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se Connecter</button>
            </form>
        </div>
        <div class="card-footer text-center">
            <small class="text-muted">ERP V1.0</small>
        </div>
    </div>
</div>

<?php 
// Pas de footer pour la page de login, mais nous allons inclure le js de Bootstrap
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
