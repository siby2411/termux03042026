<?php
require_once '../../includes/db.php';
session_start();

// Protection : seul l'admin peut accéder à cet outil
if ($_SESSION['user_role'] !== 'admin') {
    die("Accès non autorisé.");
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_pass, $_POST['user_id']);
    if ($stmt->execute()) {
        $message = "Mot de passe réinitialisé avec succès !";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Réinitialisation Mot de Passe</title></head>
<body>
    <h2>Admin - Réinitialisation Mot de Passe</h2>
    <p style="color:green;"><?php echo $message; ?></p>
    <form method="POST">
        <label>ID Utilisateur :</label><br>
        <input type="number" name="user_id" required><br>
        <label>Nouveau mot de passe :</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Valider la réinitialisation</button>
    </form>
    <br><a href="personnel.php">Retour à la liste du personnel</a>
</body>
</html>
