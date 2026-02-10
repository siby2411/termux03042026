<?php
// Fichier: /var/www/auto/liste_locations.php
include 'db_connect.php';

$message = "";

// Traitement de la suppression si un ID est passé en GET et l'action est 'delete'
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $location_id_to_delete = intval($_GET['id']);

    // TODO: Implémenter une vérification de rôle ici
    // Seul un admin devrait pouvoir supprimer des locations.

    // Avant de supprimer la location, récupérer l'ID de la voiture associée pour mettre à jour son statut
    $stmt_get_voiture_id = $conn->prepare("SELECT voiture_id FROM locations WHERE id = ?");
    $stmt_get_voiture_id->bind_param("i", $location_id_to_delete);
    $stmt_get_voiture_id->execute();
    $result_voiture_id = $stmt_get_voiture_id->get_result();
    $voiture_id_for_update = null;
    if ($result_voiture_id->num_rows > 0) {
        $row_voiture_id = $result_voiture_id->fetch_assoc();
        $voiture_id_for_update = $row_voiture_id['voiture_id'];
    }
    $stmt_get_voiture_id->close();


    $stmt = $conn->prepare("DELETE FROM locations WHERE id = ?");
    $stmt->bind_param("i", $location_id_to_delete);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Location supprimée avec succès.</p>";
        // Si la suppression est réussie et que nous avons l'ID de la voiture, mettons son statut à 'disponible'
        if ($voiture_id_for_update) {
            $stmt_update_voiture = $conn->prepare("UPDATE voitures SET statut = 'disponible' WHERE id = ?");
            $stmt_update_voiture->bind_param("i", $voiture_id_for_update);
            $stmt_update_voiture->execute();
            $stmt_update_voiture->close();
            $message .= " <p style='color:green;'>Statut de la voiture mis à jour à 'disponible'.</p>";
        }
    } else {
        $message = "<p style='color:red;'>Erreur lors de la suppression de la location : " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Récupérer la liste des locations avec les noms des clients et les détails des voitures
// ATTENTION ICI : 'l.prix_total' doit être 'l.cout_total'
$sql = "SELECT 
            l.id AS location_id, 
            l.date_debut, 
            l.date_fin, 
            l.cout_total, 
            l.statut, 
            l.date_location,
            c.nom AS client_nom, 
            c.prenom AS client_prenom,
            v.marque, 
            v.modele,
            v.prix_journalier
        FROM 
            locations l
        JOIN 
            clients c ON l.client_id = c.id
        JOIN 
            voitures v ON l.voiture_id = v.id
        ORDER BY 
            l.date_location DESC";

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Locations</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Liste des Locations</h1>
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
            <a href="ajouter_location.php">Ajouter Location</a>
            <a href="liste_locations.php" class="active">Liste Locations</a>
            <a href="ajouter_paiement.php">Ajouter Paiement</a>
            <a href="liste_paiements.php">Liste Paiements</a>
            <a href="login.php">Connexion</a>
        </nav>
    </header>

    <main>
        <h2>Locations Enregistrées</h2>
        <?php echo $message; ?>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Location</th>
                        <th>Client</th>
                        <th>Voiture</th>
                        <th>Date Début</th>
                        <th>Date Fin</th>
                        <th>Coût Total</th>
                        <th>Statut</th>
                        <th>Date Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID Location"><?php echo htmlspecialchars($row['location_id']); ?></td>
                            <td data-label="Client"><?php echo htmlspecialchars($row['client_nom'] . " " . $row['client_prenom']); ?></td>
                            <td data-label="Voiture"><?php echo htmlspecialchars($row['marque'] . " " . $row['modele']); ?></td>
                            <td data-label="Date Début"><?php echo htmlspecialchars($row['date_debut']); ?></td>
                            <td data-label="Date Fin"><?php echo htmlspecialchars($row['date_fin']); ?></td>
                            <td data-label="Coût Total"><?php echo htmlspecialchars(number_format($row['cout_total'], 2, ',', ' ')) . " €"; ?></td>
                            <td data-label="Statut"><?php echo htmlspecialchars($row['statut']); ?></td>
                            <td data-label="Date Location"><?php echo htmlspecialchars($row['date_location']); ?></td>
                            <td data-label="Actions">
                                <a href="modifier_location.php?id=<?php echo htmlspecialchars($row['location_id']); ?>">Modifier</a>
                                <a href="liste_locations.php?action=delete&id=<?php echo htmlspecialchars($row['location_id']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette location ? Ceci remettra la voiture en statut disponible.');" style="color:var(--danger-color);">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">Aucune location trouvée.</p>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 2rem;">
            <a href="ajouter_location.php" class="btn">Ajouter une nouvelle location</a>
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
