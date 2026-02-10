<?php
// Fichier: /var/www/auto/modifier_partenaire.php
include 'db_connect.php'; // Inclut le fichier de connexion à la base de données

$message = ""; // Pour stocker les messages de succès ou d'erreur
$partenaire_data = null; // Pour stocker les données du partenaire à modifier

// --- Récupérer les données du partenaire existant si un ID est fourni dans l'URL ---
if (isset($_GET['id'])) {
    $id_partenaire = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, nom, contact_email, contact_telephone, adresse FROM partenaires WHERE id = ?");
    $stmt->bind_param("i", $id_partenaire);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $partenaire_data = $result->fetch_assoc();
    } else {
        $message = "<p style='color:red;'>Partenaire non trouvé.</p>";
    }
    $stmt->close();
}

// --- Gérer la soumission du formulaire de modification ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id_partenaire = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['contact_email']);
    $telephone = trim($_POST['contact_telephone']);
    $adresse = trim($_POST['adresse']);

    // Vérification basique de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p style='color:red;'>L'adresse email n'est pas valide.</p>";
    } else {
        // Préparer la requête SQL de mise à jour
        $stmt = $conn->prepare("UPDATE partenaires SET nom=?, contact_email=?, contact_telephone=?, adresse=? WHERE id=?");
        $stmt->bind_param("ssssi", $nom, $email, $telephone, $adresse, $id_partenaire);

        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Partenaire **" . htmlspecialchars($nom) . "** mis à jour avec succès!</p>";
            // Recharger les données pour que le formulaire affiche les valeurs mises à jour
            $stmt_reload = $conn->prepare("SELECT id, nom, contact_email, contact_telephone, adresse FROM partenaires WHERE id = ?");
            $stmt_reload->bind_param("i", $id_partenaire);
            $stmt_reload->execute();
            $result_reload = $stmt_reload->get_result();
            $partenaire_data = $result_reload->fetch_assoc();
            $stmt_reload->close();
        } else {
            // Gérer les erreurs de contrainte UNIQUE (email)
            if ($conn->errno == 1062) {
                $message = "<p style='color:red;'>Erreur: L'email du partenaire existe déjà pour un autre partenaire.</p>";
            } else {
                $message = "<p style='color:red;'>Erreur lors de la mise à jour du partenaire: " . $stmt->error . "</p>";
            }
        }
        $stmt->close();
    }
}

// Si $partenaire_data est null à ce stade, c'est qu'aucun partenaire valide n'a été trouvé ou un ID n'a pas été fourni
if (!$partenaire_data && isset($id_partenaire)) { // Seulement si un ID était initialement tenté
    $message = "<p style='color:red;'>Partenaire non trouvé ou ID invalide.</p>";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Partenaire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Modifier les informations du Partenaire</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="ajouter_voiture.php">Ajouter Voiture</a>
            <a href="liste_voitures.php">Liste Voitures</a>
            <a href="ajouter_partenaire.php">Ajouter Partenaire</a>
            <a href="liste_partenaires.php">Liste Partenaires</a> <a href="ajouter_client.php">Ajouter Client</a>
            <a href="liste_clients.php">Liste Clients</a>
        </nav>
    </header>

    <main>
        <h2>Formulaire de modification de partenaire</h2>
        <?php echo $message; // Affiche les messages de succès ou d'erreur ?>
        
        <?php if ($partenaire_data): // Affiche le formulaire seulement si des données de partenaire sont disponibles ?>
        <form action="modifier_partenaire.php" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($partenaire_data['id']); ?>">

            <label for="nom">Nom du Partenaire:</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($partenaire_data['nom']); ?>" required><br>

            <label for="contact_email">Email:</label>
            <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($partenaire_data['contact_email']); ?>" required><br>

            <label for="contact_telephone">Téléphone:</label>
            <input type="text" id="contact_telephone" name="contact_telephone" value="<?php echo htmlspecialchars($partenaire_data['contact_telephone']); ?>"><br>

            <label for="adresse">Adresse:</label>
            <textarea id="adresse" name="adresse" rows="4"><?php echo htmlspecialchars($partenaire_data['adresse']); ?></textarea><br>

            <input type="submit" value="Mettre à jour le Partenaire">
        </form>
        <?php else: ?>
            <p>Impossible d'afficher le formulaire. Partenaire non trouvé ou ID manquant. Veuillez retourner à la <a href="liste_partenaires.php">liste des partenaires</a>.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Votre Entreprise de Location</p>
    </footer>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>
