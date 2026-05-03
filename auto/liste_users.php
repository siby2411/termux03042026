<?php
// Fichier: /var/www/auto/liste_users.php
include 'db_connect.php';

$message = "";

// Traitement de la suppression si un ID est passé en GET et l'action est 'delete'
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = intval($_GET['id']);

    // TODO: Implémenter une vérification de rôle ici
    // Seul un admin devrait pouvoir supprimer des utilisateurs.
    // Pour l'instant, on suppose que l'admin est le seul à y accéder.

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id_to_delete);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Utilisateur supprimé avec succès.</p>";
    } else {
        $message = "<p style='color:red;'>Erreur lors de la suppression de l'utilisateur : " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Récupérer la liste des utilisateurs
$sql = "SELECT id, username, email, role, date_creation FROM users ORDER BY username ASC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Utilisateurs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Liste des Utilisateurs</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="ajouter_voiture.php">Ajouter Voiture</a>
            <a href="liste_voitures.php">Liste Voitures</a>
            <a href="ajouter_partenaire.php">Ajouter Partenaire</a>
            <a href="liste_partenaires.php">Liste Partenaires</a>
            <a href="ajouter_client.php">Ajouter Client</a>
            <a href="liste_clients.php">Liste Clients</a>
            <a href="ajouter_user.php">Ajouter Utilisateur</a>
            <a href="liste_users.php" class="active">Liste Utilisateurs</a>
            <a href="ajouter_location.php">Ajouter Location</a>
            <a href="liste_locations.php">Liste Locations</a>
            <a href="ajouter_paiement.php">Ajouter Paiement</a>
            <a href="liste_paiements.php">Liste Paiements</a>
            <a href="login.php">Connexion</a>
        </nav>
    </header>

    <main>
        <h2>Utilisateurs Enregistrés</h2>
        <?php echo $message; ?>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Date de Création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td data-label="Nom d'utilisateur"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                            <td data-label="Rôle"><?php echo htmlspecialchars($row['role']); ?></td>
                            <td data-label="Date de Création"><?php echo htmlspecialchars($row['date_creation']); ?></td>
                            <td data-label="Actions">
                                <a href="modifier_user.php?id=<?php echo htmlspecialchars($row['id']); ?>">Modifier</a>
                                <a href="liste_users.php?action=delete&id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');" style="color:var(--danger-color);">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">Aucun utilisateur trouvé.</p>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 2rem;">
            <a href="ajouter_user.php" class="btn">Ajouter un nouvel utilisateur</a>
        </p>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Votre Entreprise de Location</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
