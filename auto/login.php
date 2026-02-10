<?php
// Fichier: /var/www/auto/login.php
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Préparer la requête pour récupérer l'utilisateur par son nom d'utilisateur
    $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Vérifier le mot de passe haché
        if (password_verify($password, $user['password_hash'])) {
            $message = "<p style='color:green;'>Connexion réussie pour " . htmlspecialchars($user['username']) . " (Rôle: " . htmlspecialchars($user['role']) . ")!</p>";
            // Ici, vous démarreriez une session et stockeriez les informations de l'utilisateur.
            // Pour l'exemple, nous nous contentons du message de succès.
            // Exemple basique de démarrage de session:
            // session_start();
            // $_SESSION['user_id'] = $user['id'];
            // $_SESSION['username'] = $user['username'];
            // $_SESSION['role'] = $user['role'];
            // header("Location: index.php"); // Rediriger vers la page d'accueil ou un tableau de bord
            // exit();
        } else {
            $message = "<p style='color:red;'>Nom d'utilisateur ou mot de passe incorrect.</p>";
        }
    } else {
        $message = "<p style='color:red;'>Nom d'utilisateur ou mot de passe incorrect.</p>";
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Connexion au Système</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="ajouter_voiture.php">Ajouter Voiture</a>
            <a href="liste_voitures.php">Liste Voitures</a>
            <a href="ajouter_partenaire.php">Ajouter Partenaire</a>
            <a href="liste_partenaires.php">Liste Partenaires</a>
            <a href="ajouter_client.php">Ajouter Client</a>
            <a href="liste_clients.php">Liste Clients</a>
            <a href="ajouter_user.php">Ajouter Utilisateur</a>
            <a href="liste_users.php">Liste Utilisateurs</a>
            <a href="login.php">Connexion</a>
        </nav>
    </header>

    <main>
        <h2>Veuillez vous connecter</h2>
        <?php echo $message; ?>
        <form action="login.php" method="post">
            <label for="username">Nom d'utilisateur:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required><br>

            <input type="submit" value="Se Connecter">
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Votre Entreprise de Location</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
