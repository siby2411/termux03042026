<?php
// Fichier: /var/www/auto/index.php
include 'db_connect.php';

// Requête pour récupérer les 3 dernières voitures disponibles pour affichage sur la page d'accueil
$sql_voitures = "SELECT id, marque, modele, annee, prix_journalier, image_url FROM voitures WHERE statut = 'disponible' ORDER BY date_ajout DESC LIMIT 3";
$result_voitures = $conn->query($sql_voitures);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Location de Voitures</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques pour la grille des voitures sur l'index */
        /* Note: Ces styles sont toujours utiles pour le conteneur voiture-card, même sans image */
        .voiture-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .voiture-card {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .voiture-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        /* Supprimez ou commentez cette section si vous avez le CSS dans style.css */
        /* .voiture-card img {
            width: 100%;
            height: 80px;
            object-fit: contain;
            border-radius: 4px;
            margin-bottom: 0.8rem;
            padding: 3px;
            background-color: #f0f0f0;
        } */
        /* Fin de la section à considérer pour suppression */

        .voiture-card h3 {
            color: var(--primary-color);
            font-size: 1.4rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .voiture-card p {
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .voiture-card .btn {
            margin-top: 1rem;
            width: fit-content;
            align-self: center;
        }
    </style>
</head>
<body>
    <header>
        <h1>Bienvenue sur notre Système de Gestion de Location de Voitures</h1>
        <nav>
            <a href="index.php" class="active">Accueil</a>
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
        <h2>Dernières Voitures Disponibles</h2>
        <div class="voiture-grid">
            <?php
            if ($result_voitures->num_rows > 0) {
                while($row = $result_voitures->fetch_assoc()) {
                    echo "<div class='voiture-card'>";
                    // Lignes qui affichaient les images ont été supprimées ici.
                    // Si vous aviez d'autres styles inline pour l'image (comme style='width: 100%; height: 180px; object-fit'), ils ne sont plus nécessaires.
                    echo "<h3>" . htmlspecialchars($row["marque"] . " " . $row["modele"]) . "</h3>";
                    echo "<p>Année: " . htmlspecialchars($row["annee"]) . "</p>";
                    echo "<p>Prix journalier: " . htmlspecialchars(number_format($row["prix_journalier"], 2, ',', ' ')) . "€</p>";
                    // Lien vers la page de détails de la voiture (remplacé 'ajouter_location.php' par 'detail_voiture.php')
                    echo "<a href='detail_voiture.php?id=" . htmlspecialchars($row["id"]) . "' class='btn'>Voir Détails</a>";
                    echo "</div>";
                }
            } else {
                echo "<p style='text-align: center; grid-column: 1 / -1;'>Aucune voiture disponible actuellement.</p>";
            }
            ?>
        </div>

        <h2 style="margin-top: 3rem;">Explorer les Modules</h2>
        <div class="module-grid">
            <div class="module-card">
                <h3>Voitures</h3>
                <p>Gérez le parc de véhicules, ajoutez de nouvelles voitures, modifiez leurs détails et statuts.</p>
                <div class="module-links">
                    <a href="ajouter_voiture.php">Ajouter</a>
                    <a href="liste_voitures.php">Voir la Liste</a>
                </div>
            </div>
            <div class="module-card">
                <h3>Clients</h3>
                <p>Enregistrez les informations de vos clients, mettez à jour leurs profils et gérez leurs données.</p>
                <div class="module-links">
                    <a href="ajouter_client.php">Ajouter</a>
                    <a href="liste_clients.php">Voir la Liste</a>
                </div>
            </div>
            <div class="module-card">
                <h3>Partenaires</h3>
                <p>Administrez les informations de vos partenaires commerciaux pour une collaboration efficace.</p>
                <div class="module-links">
                    <a href="ajouter_partenaire.php">Ajouter</a>
                    <a href="liste_partenaires.php">Voir la Liste</a>
                </div>
            </div>
            <div class="module-card">
                <h3>Utilisateurs</h3>
                <p>Gérez les comptes des employés et administrateurs du système avec des rôles spécifiques.</p>
                <div class="module-links">
                    <a href="ajouter_user.php">Ajouter</a>
                    <a href="liste_users.php">Voir la Liste</a>
                </div>
            </div>
            <div class="module-card">
                <h3>Locations</h3>
                <p>Suivez les réservations de voitures, les dates de début et de fin, et le statut des locations.</p>
                <div class="module-links">
                    <a href="ajouter_location.php">Ajouter</a>
                    <a href="liste_locations.php">Voir la Liste</a>
                </div>
            </div>
            <div class="module-card">
                <h3>Paiements</h3>
                <p>Enregistrez et suivez tous les paiements effectués pour les locations de voitures.</p>
                <div class="module-links">
                    <a href="ajouter_paiement.php">Ajouter</a>
                    <a href="liste_paiements.php">Voir la Liste</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Votre Entreprise de Location</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
