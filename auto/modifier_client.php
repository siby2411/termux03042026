<?php
// Fichier: /var/www/auto/modifier_client.php
include 'db_connect.php'; // Inclut le fichier de connexion à la base de données

$message = ""; // Pour stocker les messages de succès ou d'erreur
$client_data = null; // Pour stocker les données du client à modifier

// --- Récupérer les données du client existant si un ID est fourni dans l'URL ---
if (isset($_GET['id'])) {
    $id_client = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, nom, prenom, email, telephone, adresse, permis_conduire_num FROM clients WHERE id = ?");
    $stmt->bind_param("i", $id_client);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $client_data = $result->fetch_assoc();
    } else {
        $message = "<p style='color:red;'>Client non trouvé.</p>";
    }
    $stmt->close();
}

// --- Gérer la soumission du formulaire de modification ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id_client = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    $permis_conduire_num = trim($_POST['permis_conduire_num']);

    // Vérification basique de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p style='color:red;'>L'adresse email n'est pas valide.</p>";
    } else {
        // Préparer la requête SQL de mise à jour
        $stmt = $conn->prepare("UPDATE clients SET nom=?, prenom=?, email=?, telephone=?, adresse=?, permis_conduire_num=? WHERE id=?");
        $stmt->bind_param("ssssssi", $nom, $prenom, $email, $telephone, $adresse, $permis_conduire_num, $id_client);

        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Client **" . htmlspecialchars($nom) . " " . htmlspecialchars($prenom) . "** mis à jour avec succès!</p>";
            // Recharger les données pour que le formulaire affiche les valeurs mises à jour
            $stmt_reload = $conn->prepare("SELECT id, nom, prenom, email, telephone, adresse, permis_conduire_num FROM clients WHERE id = ?");
            $stmt_reload->bind_param("i", $id_client);
            $stmt_reload->execute();
            $result_reload = $stmt_reload->get_result();
            $client_data = $result_reload->fetch_assoc();
            $stmt_reload->close();
        } else {
            // Gérer les erreurs de contrainte UNIQUE (email ou permis)
            if ($conn->errno == 1062) {
                $message = "<p style='color:red;'>Erreur: L'email ou le numéro de permis de conduire existe déjà pour un autre client.</p>";
            } else {
                $message = "<p style='color:red;'>Erreur lors de la mise à jour du client: " . $stmt->error . "</p>";
            }
        }
        $stmt->close();
    }
}

// Si $client_data est null à ce stade, c'est qu'aucun client valide n'a été trouvé ou un ID n'a pas été fourni
if (!$client_data && isset($id_client)) { // Seulement si un ID était initialement tenté
    $message = "<p style='color:red;'>Client non trouvé ou ID invalide.</p>";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Client</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Modifier les informations du Client</h1>
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
        <h2>Formulaire de modification de client</h2>
        <?php echo $message; // Affiche les messages de succès ou d'erreur ?>
        
        <?php if ($client_data): // Affiche le formulaire seulement si des données de client sont disponibles ?>
        <form action="modifier_client.php" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($client_data['id']); ?>">

            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($client_data['nom']); ?>" required><br>

            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($client_data['prenom']); ?>" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($client_data['email']); ?>" required><br>

            <label for="telephone">Téléphone:</label>
            <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($client_data['telephone']); ?>"><br>

            <label for="adresse">Adresse:</label>
            <textarea id="adresse" name="adresse" rows="4"><?php echo htmlspecialchars($client_data['adresse']); ?></textarea><br>

            <label for="permis_conduire_num">Numéro Permis de Conduire:</label>
            <input type="text" id="permis_conduire_num" name="permis_conduire_num" value="<?php echo htmlspecialchars($client_data['permis_conduire_num']); ?>"><br>

            <input type="submit" value="Mettre à jour le Client">
        </form>
        <?php else: ?>
            <p>Impossible d'afficher le formulaire. Client non trouvé ou ID manquant. Veuillez retourner à la <a href="liste_clients.php">liste des clients</a>.</p>
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
