<?php
// Fichier: /var/www/auto/liste_partenaires.php
include 'db_connect.php';

$message = "";

// Traitement de la suppression si un ID est passé en GET et l'action est 'delete'
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $partenaire_id_to_delete = intval($_GET['id']);

    // TODO: Implémenter une vérification de rôle ici
    // Seul un admin devrait pouvoir supprimer des partenaires.

    $stmt = $conn->prepare("DELETE FROM partenaires WHERE id = ?");
    $stmt->bind_param("i", $partenaire_id_to_delete);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Partenaire supprimé avec succès.</p>";
    } else {
        $message = "<p style='color:red;'>Erreur lors de la suppression du partenaire : " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Récupérer la liste des partenaires
// Utilisation des noms de colonnes exacts: 'contact_telephone', 'contact_email', 'adresse', 'date_enregistrement'
$sql = "SELECT id, nom, contact_telephone, contact_email, adresse, date_enregistrement FROM partenaires ORDER BY nom ASC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Partenaires</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Liste des Partenaires</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="ajouter_voiture.php">Ajouter Voiture</a>
            <a href="liste_voitures.php">Liste Voitures</a>
            <a href="ajouter_partenaire.php">Ajouter Partenaire</a>
            <a href="liste_partenaires.php" class="active">Liste Partenaires</a>
            <a href="ajouter_client.php">Ajouter Client</a>
            <a href="liste_clients.php">Liste Clients</a>
            <a href="ajouter_user.php">Ajouter Utilisateur</a>
            <a href="liste_users.php">Liste Utilisateurs</a>
            <a href="ajouter_location.php">Ajouter Location</a>
            <a href="liste_locations.php">Liste Locations</a>
            <a href="ajouter_paiement.php">Ajouter Paiement</a>
            <a href="liste_paiements.php">Liste Paiements</a>
            <a href="login.php">Connexion</a>
        </nav>
    </header>

    <main>
        <h2>Partenaires Enregistrés</h2>
        <?php echo $message; ?>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Adresse</th>
                        <th>Date d'Enregistrement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td data-label="Nom"><?php echo htmlspecialchars($row['nom']); ?></td>
                            <td data-label="Téléphone"><?php echo htmlspecialchars($row['contact_telephone'] ?? 'N/A'); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($row['contact_email'] ?? 'N/A'); ?></td>
                            <td data-label="Adresse"><?php echo nl2br(htmlspecialchars($row['adresse'] ?? 'N/A')); ?></td>
                            <td data-label="Date d'Enregistrement"><?php echo htmlspecialchars($row['date_enregistrement']); ?></td>
                            <td data-label="Actions">
                                <a href="modifier_partenaire.php?id=<?php echo htmlspecialchars($row['id']); ?>">Modifier</a>
                                <a href="liste_partenaires.php?action=delete&id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce partenaire ?');" style="color:var(--danger-color);">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">Aucun partenaire trouvé.</p>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 2rem;">
            <a href="ajouter_partenaire.php" class="btn">Ajouter un nouveau partenaire</a>
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
