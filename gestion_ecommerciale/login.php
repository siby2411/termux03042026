<?php
// Fichier : login.php
// Module de connexion pour les vendeurs

session_start();
include_once 'db_connect.php';

$error_message = '';

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = db_connect();
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // 1. Recherche du vendeur par email
    $sql = "SELECT id_vendeur, nom, mot_de_passe FROM vendeurs WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $vendeur = $result->fetch_assoc();
        
        // 2. Vérification du mot de passe
        // Note: Le mot de passe stocké est '123'. Dans une vraie app, il serait hashé.
        // Si vous utilisez le hash (recommandé), remplacez par : 
        // if (password_verify($password, $vendeur['mot_de_passe'])) { ...
        
        if ($password === $vendeur['mot_de_passe']) {
            // Connexion réussie : Stockage en session
            $_SESSION['id_vendeur'] = $vendeur['id_vendeur'];
            $_SESSION['nom_vendeur'] = $vendeur['nom'];
            
            // 3. Redirection vers le tableau de bord (à créer)
            header("Location: dashboard.php"); 
            exit();
        } else {
            $error_message = "Mot de passe incorrect.";
        }
    } else {
        $error_message = "Email non trouvé.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Vendeur</title>
</head>
<body>
    <h2>Connexion à la Gestion Commerciale</h2>
    
    <?php if ($error_message): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="password">Mot de passe:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        
        <button type="submit">Se Connecter</button>
    </form>
    
    <p>Testez avec : Email: momo@gestion.local, Pass: 123</p>
</body>
</html>
