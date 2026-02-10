<?php
// Fichier: /var/www/auto/liste_voitures.php
include 'db_connect.php';

$message = "";

// 1. Traitement de la suppression (Logique standard d'administration)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $voiture_id_to_delete = intval($_GET['id']);

    // Vérification de l'existence de locations en cours ou futures pour cette voiture
    $stmt_check_loc = $conn->prepare("SELECT COUNT(*) FROM locations WHERE voiture_id = ? AND statut IN ('en_cours', 'reservee')");
    $stmt_check_loc->bind_param("i", $voiture_id_to_delete);
    $stmt_check_loc->execute();
    $stmt_check_loc->bind_result($count_locations);
    $stmt_check_loc->fetch();
    $stmt_check_loc->close();

    if ($count_locations > 0) {
        $message = "<p style='color:red;'>Impossible de supprimer cette voiture. Elle est actuellement liée à " . $count_locations . " location(s) en cours ou réservée(s).</p>";
    } else {
        $stmt = $conn->prepare("DELETE FROM voitures WHERE id = ?");
        $stmt->bind_param("i", $voiture_id_to_delete);

        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Voiture ID " . $voiture_id_to_delete . " supprimée avec succès.</p>";
        } else {
            $message = "<p style='color:red;'>Erreur lors de la suppression de la voiture : " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// 2. Variables pour la recherche par dates
$search_date_debut = $_GET['search_date_debut'] ?? '';
$search_date_fin = $_GET['search_date_fin'] ?? '';

// 3. Construction de la requête SQL pour les voitures
$sql = "SELECT v.id, v.marque, v.modele, v.annee, v.couleur, v.prix_journalier, v.statut, p.nom AS partenaire_nom
        FROM voitures v
        LEFT JOIN partenaires p ON v.partenaire_id = p.id
        WHERE 1=1"; // Clause WHERE de base

$params = [];
$types = "";

// Ajouter la condition de disponibilité par dates si les deux dates sont fournies
if (!empty($search_date_debut) && !empty($search_date_fin)) {
    // La voiture est NON DISPONIBLE si une location existante chevauche la période de recherche.
    // Condition de chevauchement : (Loc_Debut <= Search_Fin) ET (Loc_Fin >= Search_Debut)
    $sql .= " AND v.id NOT IN (
                SELECT DISTINCT voiture_id
                FROM locations
                WHERE (date_debut <= ? AND date_fin >= ?)
            )";
    
    // Les paramètres doivent correspondre aux ? dans l'ordre: [Search_Fin], [Search_Debut]
    $params[] = $search_date_fin;
    $params[] = $search_date_debut;
    $types .= "ss"; // Deux strings (dates)
} else {
    // Si pas de recherche par dates, n'afficher que les voitures 'disponible' par défaut pour un listing simple
    $sql .= " AND v.statut = 'disponible'";
}

$sql .= " ORDER BY v.marque, v.modele ASC";

// 4. Préparer et exécuter la requête
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    // Bind dynamique des paramètres
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Exécution simple si pas de paramètres de recherche
    $result = $conn->query($sql);
}

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
        <h1>Liste des Voitures</h1>
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
        <h2>Rechercher la Disponibilité</h2>
        <?php echo $message; ?>

        <form action="liste_voitures.php" method="GET" class="search-form" style="display:flex; gap:10px; align-items:flex-end; margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
            <div style="flex-grow: 1;">
                <label for="search_date_debut">Disponible à partir du :</label>
                <input type="date" id="search_date_debut" name="search_date_debut" value="<?php echo htmlspecialchars($search_date_debut); ?>" required>
            </div>
            <div style="flex-grow: 1;">
                <label for="search_date_fin">Jusqu'au :</label>
                <input type="date" id="search_date_fin" name="search_date_fin" value="<?php echo htmlspecialchars($search_date_fin); ?>" required>
            </div>

            <input type="submit" value="Rechercher" class="btn">
            <a href="liste_voitures.php" class="btn-secondary" style="background-color: #555; color: white;">Réinitialiser</a>
        </form>

        <h2>Voitures <?php echo (!empty($search_date_debut) ? "Disponibles (du " . htmlspecialchars($search_date_debut) . " au " . htmlspecialchars($search_date_fin) . ")" : "(Statut : Disponible)"); ?></h2>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Marque</th>
                        <th>Modèle</th>
                        <th>Année</th>
                        <th>Couleur</th>
                        <th>Prix/Jour</th>
                        <th>Statut</th>
                        <th>Partenaire</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td data-label="Marque"><?php echo htmlspecialchars($row['marque']); ?></td>
                            <td data-label="Modèle"><?php echo htmlspecialchars($row['modele']); ?></td>
                            <td data-label="Année"><?php echo htmlspecialchars($row['annee']); ?></td>
                            <td data-label="Couleur"><?php echo htmlspecialchars($row['couleur']); ?></td>
                            <td data-label="Prix/Jour"><?php echo htmlspecialchars(number_format($row['prix_journalier'], 2, ',', ' ')) . " €"; ?></td>
                            <td data-label="Statut"><?php echo htmlspecialchars($row['statut']); ?></td>
                            <td data-label="Partenaire"><?php echo htmlspecialchars($row['partenaire_nom'] ?? 'N/A'); ?></td>
                            <td data-label="Actions">
                                <a href="modifier_voiture.php?id=<?php echo htmlspecialchars($row['id']); ?>">Modifier</a>
                                <a href="liste_voitures.php?action=delete&id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette voiture ?');" style="color:var(--danger-color);">Supprimer</a>
                                
                                <a href="ajouter_location.php?voiture_id=<?php echo htmlspecialchars($row['id']); ?>" class="btn-small" style="background-color: var(--success-color);">Louer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">
                <?php if (!empty($search_date_debut)): ?>
                    Aucune voiture disponible pour la période sélectionnée (du <?php echo htmlspecialchars($search_date_debut); ?> au <?php echo htmlspecialchars($search_date_fin); ?>).
                <?php else: ?>
                    Aucune voiture disponible dans la base de données.
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 2rem;">
            <a href="ajouter_voiture.php" class="btn">Ajouter une nouvelle voiture</a>
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
