<?php
// Fichier: /var/www/auto/upload_diaporama.php
include 'db_connect.php'; // Pour la connexion à la base de données si nécessaire, mais ici juste pour la navigation
                         // On ne stocke pas les noms d'images dans la DB pour le diaporama simple, juste les fichiers.

$message = "";
$id_voiture = null;
$voiture_marque_modele = "";

// Récupérer l'ID de la voiture depuis l'URL (si on vient de detail_voiture.php)
if (isset($_GET['id'])) {
    $id_voiture = intval($_GET['id']);
    // Vérifier si la voiture existe et récupérer son nom pour l'affichage
    $stmt = $conn->prepare("SELECT marque, modele FROM voitures WHERE id = ?");
    $stmt->bind_param("i", $id_voiture);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $voiture_data = $result->fetch_assoc();
        $voiture_marque_modele = htmlspecialchars($voiture_data['marque'] . " " . $voiture_data['modele']);
    } else {
        $message = "<p style='color:red;'>Voiture non trouvée pour l'ID " . $id_voiture . ".</p>";
        $id_voiture = null; // Invalider l'ID si la voiture n'existe pas
    }
    $stmt->close();
}

// Traitement de l'upload lorsque le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['voiture_id'])) {
    $id_voiture_post = intval($_POST['voiture_id']);

    // Vérifier si la voiture existe réellement avant de créer le dossier
    $stmt_check = $conn->prepare("SELECT id FROM voitures WHERE id = ?");
    $stmt_check->bind_param("i", $id_voiture_post);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        $message = "<p style='color:red;'>Erreur : La voiture avec l'ID " . $id_voiture_post . " n'existe pas.</p>";
    } else {
        $upload_dir = "images/voitures/" . $id_voiture_post . "/";

        // Créer le dossier s'il n'existe pas
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) { // 0777 pour des permissions larges, à ajuster en production
                $message = "<p style='color:red;'>Erreur : Impossible de créer le dossier d'upload : " . $upload_dir . "</p>";
            }
        }

        if (empty($message) && isset($_FILES['diaporama_images'])) {
            $total_files_uploaded = 0;
            $errors_found = 0;
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_file_size = 5 * 1024 * 1024; // 5 MB

            foreach ($_FILES['diaporama_images']['name'] as $key => $name) {
                if ($_FILES['diaporama_images']['error'][$key] == UPLOAD_ERR_OK) {
                    $file_tmp_name = $_FILES['diaporama_images']['tmp_name'][$key];
                    $file_type = $_FILES['diaporama_images']['type'][$key];
                    $file_size = $_FILES['diaporama_images']['size'][$key];
                    $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                    // Générer un nom de fichier unique pour éviter les collisions
                    $new_file_name = uniqid('img_', true) . '.' . $file_ext;
                    $target_file = $upload_dir . $new_file_name;

                    // Vérifications
                    if (!in_array($file_type, $allowed_types)) {
                        $message .= "<p style='color:orange;'>Le fichier '" . htmlspecialchars($name) . "' n'est pas une image JPEG, PNG ou GIF.</p>";
                        $errors_found++;
                        continue;
                    }
                    if ($file_size > $max_file_size) {
                        $message .= "<p style='color:orange;'>Le fichier '" . htmlspecialchars($name) . "' est trop volumineux (max 5 Mo).</p>";
                        $errors_found++;
                        continue;
                    }

                    // Déplacer le fichier téléchargé
                    if (move_uploaded_file($file_tmp_name, $target_file)) {
                        $total_files_uploaded++;
                    } else {
                        $message .= "<p style='color:red;'>Erreur lors du déplacement du fichier '" . htmlspecialchars($name) . "'.</p>";
                        $errors_found++;
                    }
                } elseif ($_FILES['diaporama_images']['error'][$key] == UPLOAD_ERR_NO_FILE) {
                    // Ignorer si aucun fichier n'a été sélectionné pour un champ
                } else {
                    $message .= "<p style='color:red;'>Erreur d'upload PHP pour le fichier '" . htmlspecialchars($name) . "'. Code: " . $_FILES['diaporama_images']['error'][$key] . "</p>";
                    $errors_found++;
                }
            }

            if ($total_files_uploaded > 0) {
                $message = "<p style='color:green;'>" . $total_files_uploaded . " image(s) téléchargée(s) avec succès pour la voiture " . $id_voiture_post . ".</p>" . $message;
            }
            if ($errors_found > 0 && $total_files_uploaded == 0) {
                 $message = "<p style='color:red;'>Aucune image n'a pu être téléchargée. Veuillez vérifier les erreurs ci-dessus.</p>" . $message;
            }
            // Rediriger vers le diaporama ou la page de détails après l'upload
            if ($total_files_uploaded > 0) {
                header("Location: diaporama_voiture.php?id=" . $id_voiture_post . "&upload_success=true");
                exit();
            }
        } else if (empty($message)) {
            $message = "<p style='color:orange;'>Veuillez sélectionner au moins un fichier à télécharger.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télécharger Diaporama Images</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Télécharger des images pour le diaporama</h1>
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
        <h2>Télécharger les images du diaporama pour <?php echo ($id_voiture ? $voiture_marque_modele : 'une voiture'); ?></h2>
        <?php echo $message; ?>

        <?php if ($id_voiture): ?>
        <form action="upload_diaporama.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="voiture_id" value="<?php echo htmlspecialchars($id_voiture); ?>">
            
            <label for="diaporama_images">Sélectionnez les images à télécharger (Ctrl/Cmd pour multiple) :</label>
            <input type="file" name="diaporama_images[]" id="diaporama_images" multiple accept="image/jpeg, image/png, image/gif">
            <small>Formats acceptés : JPG, PNG, GIF. Taille max par fichier : 5 Mo.</small>
            
            <input type="submit" value="Télécharger les images">
        </form>

        <p style="text-align: center; margin-top: 2rem;">
            <a href="detail_voiture.php?id=<?php echo htmlspecialchars($id_voiture); ?>" class="btn">Retour aux détails de la voiture</a>
            <a href="diaporama_voiture.php?id=<?php echo htmlspecialchars($id_voiture); ?>" class="btn btn-secondary">Voir le diaporama existant</a>
        </p>
        <?php else: ?>
            <p style="text-align: center;">Veuillez sélectionner une voiture pour télécharger des images.</p>
            <p style="text-align: center;"><a href="liste_voitures.php" class="btn">Aller à la liste des voitures</a></p>
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
