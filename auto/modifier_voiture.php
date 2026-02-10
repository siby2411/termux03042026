<?php
// Fichier: /var/www/auto/modifier_voiture.php
include 'db_connect.php';

$message = "";
$voiture = null; // Pour stocker les données de la voiture à modifier

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Récupérer les données de la voiture à modifier
    $stmt = $conn->prepare("SELECT id, marque, modele, annee, prix_journalier, statut, partenaire_id, description, image_url FROM voitures WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $voiture = $result->fetch_assoc();
    } else {
        $message = "<p style='color:red;'>Voiture non trouvée.</p>";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $marque = $_POST['marque'];
    $modele = $_POST['modele'];
    $annee = $_POST['annee'];
    $prix_journalier = $_POST['prix_journalier'];
    $statut = $_POST['statut'];
    $partenaire_id = $_POST['partenaire_id'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    $stmt = $conn->prepare("UPDATE voitures SET marque=?, modele=?, annee=?, prix_journalier=?, statut=?, partenaire_id=?, description=?, image_url=? WHERE id=?");
    $stmt->bind_param("ssidsissi", $marque, $modele, $annee, $prix_journalier, $statut, $partenaire_id, $description, $image_url, $id);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Voiture mise à jour avec succès!</p>";
        // Recharger les données de la voiture après la mise à jour pour affichage
        $stmt_reload = $conn->prepare("SELECT id, marque, modele, annee, prix_journalier, statut, partenaire_id, description, image_url FROM voitures WHERE id = ?");
        $stmt_reload->bind_param("i", $id);
        $stmt_reload->execute();
        $result_reload = $stmt_reload->get_result();
        $voiture = $result_reload->fetch_assoc();
        $stmt_reload->close();
    } else {
        $message = "<p style='color:red;'>Erreur lors de la mise à jour: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Récupérer les partenaires pour le menu déroulant
$partenaires_sql = "SELECT id, nom FROM partenaires ORDER BY nom";
$partenaires_result = $conn->query($partenaires_sql);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Voiture</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Modifier les informations de la Voiture</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="ajouter_voiture.php">Ajouter Voiture</a>
            <a href="liste_voitures.php">Liste Voitures</a>
        </nav>
    </header>

    <main>
        <h2>Formulaire de modification de voiture</h2>
        <?php echo $message; ?>
        <?php if ($voiture): ?>
        <form action="modifier_voiture.php" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($voiture['id']); ?>">

            <label for="marque">Marque:</label>
            <input type="text" id="marque" name="marque" value="<?php echo htmlspecialchars($voiture['marque']); ?>" required><br>

            <label for="modele">Modèle:</label>
            <input type="text" id="modele" name="modele" value="<?php echo htmlspecialchars($voiture['modele']); ?>" required><br>

            <label for="annee">Année:</label>
            <input type="number" id="annee" name="annee" min="1900" max="<?php echo date("Y"); ?>" value="<?php echo htmlspecialchars($voiture['annee']); ?>" required><br>

            <label for="prix_journalier">Prix Journalier:</label>
            <input type="number" id="prix_journalier" name="prix_journalier" step="0.01" min="0" value="<?php echo htmlspecialchars($voiture['prix_journalier']); ?>" required><br>

            <label for="statut">Statut:</label>
            <select id="statut" name="statut">
                <option value="disponible" <?php if ($voiture['statut'] == 'disponible') echo 'selected'; ?>>Disponible</option>
                <option value="louee" <?php if ($voiture['statut'] == 'louee') echo 'selected'; ?>>Louée</option>
                <option value="en_maintenance" <?php if ($voiture['statut'] == 'en_maintenance') echo 'selected'; ?>>En maintenance</option>
            </select><br>

            <label for="partenaire_id">Partenaire:</label>
            <select id="partenaire_id" name="partenaire_id">
                <option value="">-- Sélectionner un partenaire --</option>
                <?php
                if ($partenaires_result->num_rows > 0) {
                    while($row = $partenaires_result->fetch_assoc()) {
                        $selected = ($voiture['partenaire_id'] == $row['id']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['nom']) . "</option>";
                    }
                }
                ?>
            </select><br>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($voiture['description']); ?></textarea><br>

            <label for="image_url">URL de l'image:</label>
            <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($voiture['image_url']); ?>"><br>

            <input type="submit" value="Mettre à jour la Voiture">
        </form>
        <?php else: ?>
            <p>Impossible d'afficher le formulaire. Veuillez retourner à la <a href="liste_voitures.php">liste des voitures</a>.</p>
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
