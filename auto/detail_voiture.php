<?php
// Fichier: /var/www/auto/detail_voiture.php
include 'db_connect.php';

$voiture_data = null;
$message = "";

if (isset($_GET['id'])) {
    $id_voiture = intval($_GET['id']);

    // Requête pour récupérer les détails de la voiture avec le nom du partenaire
    // et les nouvelles colonnes couleur, kilometrage, description.
    $stmt = $conn->prepare("SELECT v.id, v.marque, v.modele, v.annee, v.couleur, v.kilometrage, v.prix_journalier, v.statut, v.image_url, v.description, p.nom AS partenaire_nom 
                            FROM voitures v 
                            LEFT JOIN partenaires p ON v.partenaire_id = p.id
                            WHERE v.id = ?");
    $stmt->bind_param("i", $id_voiture);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $voiture_data = $result->fetch_assoc();
    } else {
        $message = "<p style='color:red;'>Voiture non trouvée.</p>";
    }
    $stmt->close();
} else {
    $message = "<p style='color:red;'>ID de voiture manquant.</p>";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Voiture</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques à cette page pour un design professionnel */
        .car-detail-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            align-items: flex-start;
            margin-top: 2rem;
        }
        .car-detail-image {
            flex: 1;
            min-width: 300px; /* Taille minimale pour l'image */
            max-width: 600px; /* Taille maximale pour l'image */
            box-shadow: var(--box-shadow);
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            padding: 1rem;
            text-align: center; /* Centrer l'image si elle est plus petite que son conteneur */
        }
        .car-detail-image img {
            width: 100%;
            max-height: 400px; /* Limite la hauteur de l'image agrandie */
            height: auto;
            object-fit: contain; /* Conserver les proportions de l'image */
            display: block;
            border-radius: 4px;
            margin: 0 auto; /* Pour centrer l'image */
            background-color: #f0f0f0; /* Fond derrière l'image */
        }
        .car-detail-info {
            flex: 2;
            min-width: 300px;
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--box-shadow);
        }
        .car-detail-info h2 {
            text-align: left;
            margin-top: 0;
            color: var(--primary-color);
            font-size: 2.2rem;
            margin-bottom: 1rem;
        }
        .car-detail-info p {
            font-size: 1.1rem;
            margin-bottom: 0.8rem;
            color: var(--text-color);
        }
        .car-detail-info p strong {
            color: var(--background-dark);
            margin-right: 0.5rem;
        }
        .car-detail-info .description {
            margin-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
            font-style: italic;
            color: var(--secondary-color);
        }
        .action-buttons {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap; /* Permet aux boutons de passer à la ligne sur petits écrans */
        }
        .action-buttons a {
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
            white-space: nowrap; /* Empêche les boutons de se couper */
        }
        .action-buttons .btn-primary {
            background-color: var(--primary-color);
            color: var(--text-light);
        }
        .action-buttons .btn-primary:hover {
            background-color: #0056b3;
        }
        .action-buttons .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--text-light);
        }
        .action-buttons .btn-secondary:hover {
            background-color: #5a6268;
        }
        /* Style spécifique pour le bouton "Télécharger images diaporama" */
        .action-buttons .btn { /* Utilise la classe .btn de base définie dans style.css, ou vous pouvez définir une nouvelle classe ici */
            background-color: #6c757d; /* Couleur gris secondaire, pour le différencier */
            color: var(--text-light);
        }
        .action-buttons .btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <header>
        <h1>Détails de la Voiture</h1>
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
        <?php echo $message; ?>

        <?php if ($voiture_data): ?>
        <div class="car-detail-container">
            <div class="car-detail-image">
                <?php
                $image_src = 'placeholder.png'; // Image par défaut
                if (!empty($voiture_data["image_url"]) && file_exists($voiture_data["image_url"])) {
                    $image_src = htmlspecialchars($voiture_data["image_url"]);
                }
                echo "<img src='" . $image_src . "' alt='Image " . htmlspecialchars($voiture_data["marque"] . " " . $voiture_data["modele"]) . "'>";
                ?>
            </div>
            <div class="car-detail-info">
                <h2><?php echo htmlspecialchars($voiture_data["marque"] . " " . $voiture_data["modele"]); ?></h2>
                <p><strong>Année:</strong> <?php echo htmlspecialchars($voiture_data["annee"]); ?></p>
                <p><strong>Couleur:</strong> <?php echo htmlspecialchars($voiture_data["couleur"]); ?></p>
                <p><strong>Kilométrage:</strong> <?php echo htmlspecialchars(number_format($voiture_data["kilometrage"], 0, ',', ' ')) . " km"; ?></p>
                <p><strong>Prix Journalier:</strong> <?php echo htmlspecialchars(number_format($voiture_data["prix_journalier"], 2, ',', ' ')) . " €"; ?></p>
                <p><strong>Statut:</strong> <?php echo htmlspecialchars($voiture_data["statut"]); ?></p>
                <p><strong>Partenaire:</strong> <?php echo htmlspecialchars($voiture_data["partenaire_nom"] ?? 'N/A'); ?></p>
                
                <?php if (!empty($voiture_data["description"])): ?>
                    <p class="description"><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($voiture_data["description"])); ?></p>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="ajouter_location.php?voiture_id=<?php echo htmlspecialchars($voiture_data['id']); ?>" class="btn-primary">Louer cette voiture</a>
                    <a href="diaporama_voiture.php?id=<?php echo htmlspecialchars($voiture_data['id']); ?>" class="btn-secondary">Voir le Diaporama</a>
                    <a href="upload_diaporama.php?id=<?php echo htmlspecialchars($voiture_data['id']); ?>" class="btn">Télécharger Images Diaporama</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <p style="text-align: center; margin-top: 3rem;"><a href="liste_voitures.php" class="btn">Retour à la liste des voitures</a></p>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Votre Entreprise de Location</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
```http://googleusercontent.com/image_generation_content/13
