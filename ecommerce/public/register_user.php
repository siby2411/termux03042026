<?php
require_once __DIR__ . "/../src/includes/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // admin, user, fournisseur, client

    // hash du mot de passe
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed, $role);

    if ($stmt->execute()) {
        echo "Utilisateur créé avec succès.";
    } else {
        echo "Erreur : " . $stmt->error;
    }
}
?>
<form method="post">
    <input type="text" name="username" placeholder="Nom utilisateur" required>
    <input type="email" name="email" placeholder="Email">
    <input type="password" name="password" placeholder="Mot de passe" required>
    <select name="role">
        <option value="admin">Admin</option>
        <option value="user">User</option>
        <option value="client">Client</option>
        <option value="fournisseur">Fournisseur</option>
    </select>
    <button type="submit">Créer</button>
</form>

