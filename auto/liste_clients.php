<?php
// Fichier: /var/www/auto/liste_clients.php
include 'db_connect.php'; // Inclut le fichier de connexion à la base de données

$message = ""; // Pour stocker les messages de succès ou d'erreur

// --- Gérer l'action de suppression ---
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id_a_supprimer = intval($_GET['id']); // Assure que l'ID est un entier

    // Préparer et exécuter la requête SQL de suppression
    $stmt_delete = $conn->prepare("DELETE FROM clients WHERE id = ?");
    $stmt_delete->bind_param("i", $id_a_supprimer);

    if ($stmt_delete->execute()) {
        $message = "<p style='color:green;'>Client supprimé avec succès!</p>";
    } else {
        $message = "<p style='color:red;'>Erreur lors de la suppression du client: " . $stmt_delete->error . "</p>";
    }
    $stmt_delete->close(); // Ferme le statement préparé

    // Rediriger pour éviter la resoumission du formulaire à l'actualisation
    header("Location: liste_clients.php");
    exit();
}

// --- Requête pour récupérer tous les clients ---
$sql = "SELECT id, nom, prenom, email, telephone, adresse, permis_conduire_num, date_enregistrement FROM clients ORDER BY nom, prenom";
$result = $conn->query($sql); // Exécute la requête

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Clients</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Liste de tous les Clients</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="ajouter_voiture.php">Ajouter Voiture</a>
            <a href="liste_voitures.php">Liste Voitures</a>
            <a href="ajouter_partenaire.php">Ajouter Partenaire</a>
            <a href="ajouter_client.php">Ajouter Client</a>
            <a href="liste_clients.php">Liste Clients</a>
        </nav>
    </header>

    <main>
        <h2>Clients enregistrés</h2>
        <?php echo $message; // Affiche les messages de succès ou d'erreur ?>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                    <th>Permis de Conduire</th>
                    <th>Date d'Enregistrement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Boucle sur chaque ligne de résultat
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nom"]) . " " . htmlspecialchars($row["prenom"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["telephone"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["adresse"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["permis_conduire_num"]) . "</td>";
                        echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($row["date_enregistrement"]))) . "</td>";
                        echo "<td>";
                        echo "<a href='modifier_client.php?id=" . htmlspecialchars($row["id"]) . "'>Modifier</a> | ";
                        // Lien de suppression avec confirmation JavaScript
                        echo "<a href='liste_clients.php?action=supprimer&id=" . htmlspecialchars($row["id"]) . "' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer ce client? Cette action est irréversible.');\">Supprimer</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    // Si aucun client n'est enregistré
                    echo "<tr><td colspan='8'>Aucun client enregistré.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <p><a href="ajouter_client.php" class="btn">Ajouter un nouveau client</a></p>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Votre Entreprise de Location</p>
    </footer>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>
