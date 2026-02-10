<?php
// Fichier: /var/www/auto/liste_voitures.php
include 'db_connect.php';

$message = "";

// --- Gérer l'action de suppression ---
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id_a_supprimer = intval($_GET['id']);

    // Vérifier si des locations sont associées à cette voiture
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM locations WHERE voiture_id = ?");
    $stmt_check->bind_param("i", $id_a_supprimer);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_row();
    $count_locations = $row_check[0];
    $stmt_check->close();

    if ($count_locations > 0) {
        $message = "<p style='color:red;'>Erreur: Impossible de supprimer cette voiture car " . $count_locations . " location(s) lui est/sont associée(s). Veuillez d'abord réaffecter ou supprimer ces locations.</p>";
    } else {
        // Optionnel: Supprimer l'image si elle existe sur le serveur
        $stmt_get_image = $conn->prepare("SELECT image_url FROM voitures WHERE id = ?");
        $stmt_get_image->bind_param("i", $id_a_supprimer);
        $stmt_get_image->execute();
        $result_image = $stmt_get_image->get_result();
        if ($row_image = $result_image->fetch_assoc()) {
            if (!empty($row_image['image_url']) && file_exists($row_image['image_url'])) {
                unlink($row_image['image_url']); // Supprime le fichier image
            }
        }
        $stmt_get_image->close();

        // Préparer et exécuter la requête SQL de suppression
        $stmt_delete = $conn->prepare("DELETE FROM voitures WHERE id = ?");
        $stmt_delete->bind_param("i", $id_a_supprimer);

        if ($stmt_delete->execute()) {
            $message = "<p style='color:green;'>Voiture supprimée avec succès!</p>";
        } else {
            $message = "<p style='color:red;'>Erreur lors de la suppression de la voiture: " . $stmt_delete->error . "</p>";
        }
        $stmt_delete->close();
    }

    header("Location: liste_voitures.php"); // Redirige pour éviter la resoumission
    exit();
}


// --- Requête pour récupérer toutes les voitures, y compris le nom du partenaire ---
$sql = "SELECT v.id, v.marque, v.modele, v.annee, v.prix_journalier, v.statut, v.image_url, p.nom AS partenaire_nom 
        FROM voitures v 
        LEFT JOIN partenaires p ON v.partenaire_id = p.id
        ORDER BY v.marque, v.modele";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Voitures</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Liste de toutes les Voitures</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="ajouter_voiture.php">Ajouter Voiture</a>
            <a href="liste_voitures.php" class="active">Liste Voitures</a>
            <a href="ajouter_partenaire.php">Ajouter Partenaire</a>
            <a href="liste_partenaires.php">Liste Partenaires</a>
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
        <h2>Voitures disponibles et en stock</h2>
        <?php echo $message; // Affiche les messages de succès ou d'erreur ?>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Marque</th>
                    <th>Modèle</th>
                    <th>Année</th>
                    <th>Prix Journalier (€)</th>
                    <th>Statut</th>
                    <th>Partenaire</th>
                    <th>Actions</th>
                    <th>Détails</th> </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Boucle sur chaque ligne de résultat
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                        echo "<td>";
                        if (!empty($row["image_url"]) && file_exists($row["image_url"])) {
                            echo "<img src='" . htmlspecialchars($row["image_url"]) . "' alt='Image " . htmlspecialchars($row["marque"] . " " . $row["modele"]) . "'>";
                        } else {
                            echo "<img src='placeholder.png' alt='Image par défaut'>";
                        }
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($row["marque"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["modele"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["annee"]) . "</td>";
                        echo "<td>" . htmlspecialchars(number_format($row["prix_journalier"], 2, ',', ' ')) . "</td>";
                        echo "<td>" . htmlspecialchars($row["statut"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["partenaire_nom"] ?? 'N/A') . "</td>";
                        echo "<td>";
                        echo "<a href='modifier_voiture.php?id=" . htmlspecialchars($row["id"]) . "'>Modifier</a> | ";
                        echo "<a href='liste_voitures.php?action=supprimer&id=" . htmlspecialchars($row["id"]) . "' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer cette voiture? Cette action est irréversible et supprimera l\'image associée.');\">Supprimer</a>";
                        echo "</td>";
                        // Lien vers la page de détails
                        echo "<td><a href='detail_voiture.php?id=" . htmlspecialchars($row["id"]) . "'>Voir Détails</a></td>";
                        echo "</tr>";
                    }
                } else {
                    // Si aucune voiture n'est enregistrée
                    echo "<tr><td colspan='10'>Aucune voiture enregistrée.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <p><a href="ajouter_voiture.php" class="btn">Ajouter une nouvelle voiture</a></p>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Votre Entreprise de Location</p>
    </footer>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>
