<?php
// Fichier: /var/www/auto/modifier_user.php
include 'db_connect.php';

$message = "";
$user_data = null;

// --- Récupérer les données de l'utilisateur existant ---
if (isset($_GET['id'])) {
    $id_user = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?"); // Ne pas récupérer le mot de passe!
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $user_data = $result->fetch_assoc();
    } else {
        $message = "<p style='color:red;'>Utilisateur non trouvé.</p>";
    }
    $stmt->close();
}

// --- Gérer la soumission du formulaire de modification ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id_user = intval($_POST['id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = $_POST['password']; // Nouveau mot de passe (peut être vide)

    // Vérification basique de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p style='color:red;'>L'adresse email n'est pas valide.</p>";
    } 
    // Vérification de la longueur du mot de passe si fourni
    else if (!empty($password) && strlen($password) < 6) {
        $message = "<p style='color:red;'>Le nouveau mot de passe doit contenir au moins 6 caractères s'il est fourni.</p>";
    }
    else {
        $sql_update = "UPDATE users SET username=?, email=?, role=? ";
        $types = "sss";
        $params = [$username, $email, $role];

        // Si un nouveau mot de passe est fourni, le hacher et l'inclure dans la mise à jour
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update .= ", password_hash=? ";
            $types .= "s";
            $params[] = $hashed_password;
        }

        $sql_update .= " WHERE id=?";
        $types .= "i";
        $params[] = $id_user;

        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param($types, ...$params); // Utilisation de l'opérateur spread pour bind_param

        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Utilisateur **" . htmlspecialchars($username) . "** mis à jour avec succès!</p>";
            // Recharger les données pour que le formulaire affiche les valeurs mises à jour
            $stmt_reload = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
            $stmt_reload->bind_param("i", $id_user);
            $stmt_reload->execute();
            $result_reload = $stmt_reload->get_result();
            $user_data = $result_reload->fetch_assoc();
            $stmt_reload->close();
        } else {
            if ($conn->errno == 1062) {
                $message = "<p style='color:red;'>Erreur: Le nom d'utilisateur ou l'email existe déjà pour un autre utilisateur.</p>";
            } else {
                $message = "<p style='color:red;'>Erreur lors de la mise à jour de l'utilisateur: " . $stmt->error . "</p>";
            }
        }
        $stmt->close();
    }
}

if (!$user_data && isset($id_user)) {
    $message = "<p style='color:red;'>Utilisateur non trouvé ou ID invalide.</p>";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Utilisateur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Modifier les informations de l'Utilisateur</h1>
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
        <h2>Formulaire de modification d'utilisateur</h2>
        <?php echo $message; ?>
        
        <?php if ($user_data): ?>
        <form action="modifier_user.php" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user_data['id']); ?>">

            <label for="username">Nom d'utilisateur:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required><br>

            <label for="role">Rôle:</label>
            <select id="role" name="role">
                <option value="admin" <?php echo ($user_data['role'] == 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                <option value="employee" <?php echo ($user_data['role'] == 'employee') ? 'selected' : ''; ?>>Employé</option>
            </select><br>

            <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer):</label>
            <input type="password" id="password" name="password"><br>

            <input type="submit" value="Mettre à jour l'Utilisateur">
        </form>
        <?php else: ?>
            <p>Impossible d'afficher le formulaire. Utilisateur non trouvé ou ID manquant. Veuillez retourner à la <a href="liste_users.php">liste des utilisateurs</a>.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Votre Entreprise de Location</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
