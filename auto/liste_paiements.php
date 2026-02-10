<?php
// Fichier: /var/www/auto/liste_paiements.php
include 'db_connect.php';

$message = "";

// Traitement de la suppression si un ID est passé en GET et l'action est 'delete'
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $paiement_id_to_delete = intval($_GET['id']);

    // TODO: Implémenter une vérification de rôle ici
    // Seul un admin devrait pouvoir supprimer des paiements.

    $stmt = $conn->prepare("DELETE FROM paiements WHERE id = ?");
    $stmt->bind_param("i", $paiement_id_to_delete);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Paiement supprimé avec succès.</p>";
    } else {
        $message = "<p style='color:red;'>Erreur lors de la suppression du paiement : " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Récupérer la liste des paiements avec les détails des locations, clients et voitures
// ATTENTION ICI : 'p.statut_paiement' doit être 'p.statut'
$sql = "SELECT
            p.id AS paiement_id,
            p.montant,
            p.date_paiement,
            p.methode_paiement,
            p.statut, -- Nom de colonne corrigé
            l.id AS location_id,
            l.date_debut,
            l.date_fin,
            l.cout_total,
            c.nom AS client_nom,
            c.prenom AS client_prenom,
            v.marque,
            v.modele
        FROM
            paiements p
        JOIN
            locations l ON p.location_id = l.id
        JOIN
            clients c ON l.client_id = c.id
        JOIN
            voitures v ON l.voiture_id = v.id
        ORDER BY
            p.date_paiement DESC";

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Paiements</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Liste des Paiements</h1>
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
            <a href="liste_locations.php">Liste Locations</a>
            <a href="ajouter_paiement.php">Ajouter Paiement</a>
            <a href="liste_paiements.php" class="active">Liste Paiements</a>
            <a href="login.php">Connexion</a>
        </nav>
    </header>

    <main>
        <h2>Paiements Enregistrés</h2>
        <?php echo $message; ?>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Paiement</th>
                        <th>Location ID</th>
                        <th>Client</th>
                        <th>Voiture</th>
                        <th>Date Début Location</th>
                        <th>Date Fin Location</th>
                        <th>Coût Total Location</th>
                        <th>Montant Payé</th>
                        <th>Méthode</th>
                        <th>Statut Paiement</th>
                        <th>Date Paiement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID Paiement"><?php echo htmlspecialchars($row['paiement_id']); ?></td>
                            <td data-label="Location ID"><?php echo htmlspecialchars($row['location_id']); ?></td>
                            <td data-label="Client"><?php echo htmlspecialchars($row['client_nom'] . " " . $row['client_prenom']); ?></td>
                            <td data-label="Voiture"><?php echo htmlspecialchars($row['marque'] . " " . $row['modele']); ?></td>
                            <td data-label="Date Début Loc."><?php echo htmlspecialchars($row['date_debut']); ?></td>
                            <td data-label="Date Fin Loc."><?php echo htmlspecialchars($row['date_fin']); ?></td>
                            <td data-label="Coût Total Loc."><?php echo htmlspecialchars(number_format($row['cout_total'], 2, ',', ' ')) . " €"; ?></td>
                            <td data-label="Montant Payé"><?php echo htmlspecialchars(number_format($row['montant'], 2, ',', ' ')) . " €"; ?></td>
                            <td data-label="Méthode"><?php echo htmlspecialchars($row['methode_paiement']); ?></td>
                            <td data-label="Statut Paiement"><?php echo htmlspecialchars($row['statut']); ?></td>
                            <td data-label="Date Paiement"><?php echo htmlspecialchars($row['date_paiement']); ?></td>
                            <td data-label="Actions">
                                <a href="modifier_paiement.php?id=<?php echo htmlspecialchars($row['paiement_id']); ?>">Modifier</a>
                                <a href="liste_paiements.php?action=delete&id=<?php echo htmlspecialchars($row['paiement_id']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?');" style="color:var(--danger-color);">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">Aucun paiement trouvé.</p>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 2rem;">
            <a href="ajouter_paiement.php" class="btn">Ajouter un nouveau paiement</a>
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
