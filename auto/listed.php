<?php
// Fichier: /var/www/auto/liste_voitures.php
include 'db_connect.php';

$message = "";

// Traitement de la suppression (si vous avez cette fonctionnalité et les droits admin)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    // ... votre logique de suppression existante pour les voitures ...
}

// Variables pour la recherche par dates
$search_date_debut = $_GET['search_date_debut'] ?? '';
$search_date_fin = $_GET['search_date_fin'] ?? '';

// Construction de la requête SQL pour les voitures
$sql = "SELECT v.id, v.marque, v.modele, v.annee, v.couleur, v.prix_journalier, v.statut, p.nom AS partenaire_nom
        FROM voitures v
        LEFT JOIN partenaires p ON v.partenaire_id = p.id
        WHERE 1=1"; // Clause WHERE de base pour faciliter l'ajout de conditions

$params = [];
$types = "";

// Ajouter la condition de disponibilité par dates si les deux dates sont fournies
if (!empty($search_date_debut) && !empty($search_date_fin)) {
    // Trouver les voitures qui NE SONT PAS disponibles entre les dates spécifiées
    // Une voiture est non disponible si une location existante chevauche la période de recherche
    $sql .= " AND v.id NOT IN (
                SELECT DISTINCT voiture_id
                FROM locations
                WHERE (date_debut <= ? AND date_fin >= ?) OR (date_debut >= ? AND date_debut <= ?) OR (date_fin >= ? AND date_fin <= ?)
            )";
    $params[] = $search_date_fin;
    $params[] = $search_date_debut;
    $params[] = $search_date_debut;
    $params[] = $search_date_fin;
    $params[] = $search_date_debut;
    $params[] = $search_date_fin;
    $types .= "ssssss"; // Six strings pour les dates
} else {
    // Si pas de recherche par dates, n'afficher que les voitures "disponibles" par défaut
    $sql .= " AND v.statut = 'disponible'";
}

$sql .= " ORDER BY v.marque, v.modele ASC";

// Préparer et exécuter la requête
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
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
        <h2>Rechercher et Voir les Voitures</h2>
        <?php echo $message; ?>

        <form action="liste_voitures.php" method="GET" class="search-form">
            <label for="search_date_debut">Disponible à partir du :</label>
            <input type="date" id="search_date_debut" name="search_date_debut" value="<?php echo htmlspecialchars($search_date_debut); ?>">

            <label for="search_date_fin">Jusqu'au :</label>
            <input type="date" id="search_date_fin" name="search_date_fin" value="<?php echo htmlspecialchars($search_date_fin); ?>">

            <input type="submit" value="Rechercher Disponibilité">
            <a href="liste_voitures.php" class="btn-secondary">Réinitialiser</a>
        </form>

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
                                <a href="detail_voiture.php?id=<?php echo htmlspecialchars($row['id']); ?>">Détails</a>
                                <?php if (/* condition pour afficher les boutons de modification/suppression */ true): ?>
                                    <a href="modifier_voiture.php?id=<?php echo htmlspecialchars($row['id']); ?>">Modifier</a>
                                    <a href="liste_voitures.php?action=delete&id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette voiture ?');" style="color:var(--danger-color);">Supprimer</a>
                                <?php endif; ?>
                                <a href="ajouter_location.php?voiture_id=<?php echo htmlspecialchars($row['id']); ?>" class="btn">Louer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">Aucune voiture trouvée pour la période sélectionnée.</p>
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
