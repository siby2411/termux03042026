

<?php
// Fichier: /var/www/auto/modifier_paiement.php
include 'db_connect.php';

$message = "";
$paiement_data = null;

// --- Récupérer les données du paiement existant si un ID est fourni dans l'URL ---
if (isset($_GET['id'])) {
    $id_paiement = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, location_id, montant, methode_paiement, statut_paiement FROM paiements WHERE id = ?");
    $stmt->bind_param("i", $id_paiement);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $paiement_data = $result->fetch_assoc();
    } else {
        $message = "<p style='color:red;'>Paiement non trouvé.</p>";
    }
    $stmt->close();
}

// --- Gérer la soumission du formulaire de modification ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id_paiement = intval($_POST['id']);
    $location_id = intval($_POST['location_id']);
    $montant = floatval($_POST['montant']);
    $methode_paiement = trim($_POST['methode_paiement']);
    $statut_paiement = trim($_POST['statut_paiement']);

    if (empty($location_id) || empty($montant) || empty($methode_paiement) || empty($statut_paiement)) {
        $message = "<p style='color:red;'>Veuillez remplir tous les champs obligatoires.</p>";
    } elseif ($montant <= 0) {
        $message = "<p style='color:red;'>Le montant du paiement doit être positif.</p>";
    } else {
        $stmt = $conn->prepare("UPDATE paiements SET location_id=?, montant=?, methode_paiement=?, statut_paiement=? WHERE id=?");
        $stmt->bind_param("idssi", $location_id, $montant, $methode_paiement, $statut_paiement, $id_paiement);

        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Paiement mis à jour avec succès!</p>";
            // Recharger les données pour que le formulaire affiche les valeurs mises à jour
            $stmt_reload = $conn->prepare("SELECT id, location_id, montant, methode_paiement, statut_paiement FROM paiements WHERE id = ?");
            $stmt_reload->bind_param("i", $id_paiement);
            $stmt_reload->execute();
            $result_reload = $stmt_reload->get_result();
            $paiement_data = $result_reload->fetch_assoc();
            $stmt_reload->close();
        } else {
            $message = "<p style='color:red;'>Erreur lors de la mise à jour du paiement: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

if (!$paiement_data && isset($id_paiement)) {
    $message = "<p style='color:red;'>Paiement non trouvé ou ID invalide.</p>";
}

// Récupérer les locations pour le menu déroulant
$locations_sql = "SELECT 
                    l.id, v.marque, v.modele, c.nom AS client_nom, c.prenom AS client_prenom, l.prix_total
                  FROM 
                    locations l
                  JOIN 
                    voitures v ON l.voiture_id = v.id
                  JOIN 
                    clients c ON l.client_id = c.id
                  ORDER BY l.id DESC";
$locations_result = $conn->query($locations_sql);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Paiement</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Modifier les informations du Paiement</h1>
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
            <a href="liste_paiements.php">Liste Paiements</a>
            <a href="login.php">Connexion</a>
        </nav>
    </header>

    <main>
        <h2>Formulaire de modification de paiement</h2>
        <?php echo $message; ?>
        
        <?php if ($paiement_data): ?>
        <form action="modifier_paiement.php" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($paiement_data['id']); ?>">

            <label for="location_id">Location:</label>
            <select id="location_id" name="location_id" required>
                <option value="">-- Sélectionner une location --</option>
                <?php
                if ($locations_result->num_rows > 0) {
                    while($row = $locations_result->fetch_assoc()) {
                        $selected = ((string)$paiement_data['location_id'] == (string)$row['id']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row['id']) . "' " . $selected . ">" . htmlspecialchars("ID " . $row['id'] . ": " . $row['marque'] . " " . $row['modele'] . " - " . $row['client_nom'] . " " . $row['client_prenom'] . " (Total: " . number_format($row['prix_total'], 2, ',', ' ') . "€)") . "</option>";
                    }
                } else {
                    echo "<option value='' disabled>Aucune location active</option>";
                }
                ?>
            </select><br>

            <label for="montant">Montant (€):</label>
            <input type="number" id="montant" name="montant" step="0.01" min="0" value="<?php echo htmlspecialchars($paiement_data['montant']); ?>" required><br>

            <label for="methode_paiement">Méthode de paiement:</label>
            <select id="methode_paiement" name="methode_paiement">
                <option value="Carte Bancaire" <?php echo ($paiement_data['methode_paiement'] == 'Carte Bancaire') ? 'selected' : ''; ?>>Carte Bancaire</option>
                <option value="Virement" <?php echo ($paiement_data['methode_paiement'] == 'Virement') ? 'selected' : ''; ?>>Virement</option>
                <option value="Espèces" <?php echo ($paiement_data['methode_paiement'] == 'Espèces') ? 'selected' : ''; ?>>Espèces</option>
                <option value="Chèque" <?php echo ($paiement_data['methode_paiement'] == 'Chèque') ? 'selected' : ''; ?>>Chèque</option>
            </select><br>

            <label for="statut_paiement">Statut du paiement:</label>
            <select id="statut_paiement" name="statut_paiement">
                <option value="Payé" <?php echo ($paiement_data['statut_paiement'] == 'Payé') ? 'selected' : ''; ?>>Payé</option>
                <option value="En attente" <?php echo ($paiement_data['statut_paiement'] == 'En attente') ? 'selected' : ''; ?>>En attente</option>
                <option value="Remboursé" <?php echo ($paiement_data['statut_paiement'] == 'Remboursé') ? 'selected' : ''; ?>>Remboursé</option>
                <option value="Partiel" <?php echo ($paiement_data['statut_paiement'] == 'Partiel') ? 'selected' : ''; ?>>Partiel</option>
            </select><br>

            <input type="submit" value="Mettre à jour le Paiement">
        </form>
        <?php else: ?>
            <p>Impossible d'afficher le formulaire. Paiement non trouvé ou ID manquant. Veuillez retourner à la <a href="liste_paiements.php">liste des paiements</a>.</p>
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


