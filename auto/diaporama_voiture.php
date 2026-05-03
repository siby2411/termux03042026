<?php
// Fichier: /var/www/auto/diaporama_voiture.php
include 'db_connect.php';

$voiture_data = null;
$message = "";
$image_folder = "";
$images = [];

if (isset($_GET['id'])) {
    $id_voiture = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT id, marque, modele FROM voitures WHERE id = ?");
    $stmt->bind_param("i", $id_voiture);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $voiture_data = $result->fetch_assoc();
        
        // Chemin du dossier d'images pour cette voiture
        $image_folder = "images/voitures/" . htmlspecialchars($voiture_data['id']) . "/";

        // Récupérer la liste des images dans ce dossier
        if (is_dir($image_folder)) {
            $files = scandir($image_folder);
            foreach ($files as $file) {
                if ($file != "." && $file != ".." && in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $images[] = $image_folder . $file;
                }
            }
        }
        
        if (empty($images)) {
            $message = "<p style='color:orange;'>Aucune image trouvée pour cette voiture dans le dossier " . htmlspecialchars($image_folder) . ".</p>";
            // Ajouter l'image placeholder si aucune image n'est trouvée
            $images[] = 'placeholder.png';
        }

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
    <title>Diaporama <?php echo htmlspecialchars($voiture_data['marque'] ?? '') . " " . htmlspecialchars($voiture_data['modele'] ?? ''); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .slideshow-container {
            max-width: 800px;
            position: relative;
            margin: auto;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            background-color: #fff;
        }

        .mySlides {
            display: none;
            width: 100%;
            text-align: center;
        }

        .mySlides img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain; /* Ou 'cover' selon le rendu désiré */
            border-bottom: 1px solid var(--border-color);
        }

        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            padding: 16px;
            margin-top: -22px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.6s ease;
            border-radius: 0 3px 3px 0;
            user-select: none;
            background-color: rgba(0,0,0,0.5);
        }

        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }

        .prev:hover, .next:hover {
            background-color: rgba(0,0,0,0.8);
        }

        .text {
            color: var(--text-color);
            font-size: 15px;
            padding: 8px 12px;
            position: absolute;
            bottom: 8px;
            width: 100%;
            text-align: center;
            background-color: rgba(255,255,255,0.7);
        }

        .numbertext {
            color: #f2f2f2;
            font-size: 12px;
            padding: 8px 12px;
            position: absolute;
            top: 0;
        }

        .dot-container {
            text-align: center;
            padding: 20px;
        }

        .dot {
            cursor: pointer;
            height: 15px;
            width: 15px;
            margin: 0 2px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.6s ease;
        }

        .active-dot, .dot:hover {
            background-color: #717171;
        }

        /* Fading animation */
        .fade {
            animation-name: fade;
            animation-duration: 1.5s;
        }

        @keyframes fade {
            from {opacity: .4} 
            to {opacity: 1}
        }
    </style>
</head>
<body>
    <header>
        <h1>Diaporama de la Voiture <?php echo htmlspecialchars($voiture_data['marque'] ?? '') . " " . htmlspecialchars($voiture_data['modele'] ?? ''); ?></h1>
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
        <?php echo $message; ?>

        <?php if ($voiture_data && !empty($images)): ?>
            <div class="slideshow-container">
                <?php foreach ($images as $key => $img_path): ?>
                    <div class="mySlides fade">
                        <div class="numbertext"><?php echo ($key + 1) . " / " . count($images); ?></div>
                        <img src="<?php echo htmlspecialchars($img_path); ?>" alt="<?php echo htmlspecialchars($voiture_data['marque'] . " " . $voiture_data['modele'] . " - Vue " . ($key + 1)); ?>">
                        <div class="text"><?php echo htmlspecialchars($voiture_data['marque'] . " " . $voiture_data['modele']); ?></div>
                    </div>
                <?php endforeach; ?>

                <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                <a class="next" onclick="plusSlides(1)">&#10095;</a>
            </div>
            <br>
            <div class="dot-container">
                <?php foreach ($images as $key => $img_path): ?>
                    <span class="dot" onclick="currentSlide(<?php echo ($key + 1); ?>)"></span>
                <?php endforeach; ?>
            </div>

            <script>
                let slideIndex = 1;
                showSlides(slideIndex);

                function plusSlides(n) {
                    showSlides(slideIndex += n);
                }

                function currentSlide(n) {
                    showSlides(slideIndex = n);
                }

                function showSlides(n) {
                    let i;
                    let slides = document.getElementsByClassName("mySlides");
                    let dots = document.getElementsByClassName("dot");
                    if (n > slides.length) {slideIndex = 1}    
                    if (n < 1) {slideIndex = slides.length}
                    for (i = 0; i < slides.length; i++) {
                        slides[i].style.display = "none";  
                    }
                    for (i = 0; i < dots.length; i++) {
                        dots[i].className = dots[i].className.replace(" active-dot", "");
                    }
                    if (slides.length > 0) { // S'assurer qu'il y a des slides à afficher
                        slides[slideIndex-1].style.display = "block";  
                        dots[slideIndex-1].className += " active-dot";
                    }
                }
            </script>
        <?php else: ?>
            <p style="text-align: center;">Impossible d'afficher le diaporama. Veuillez vérifier l'ID de la voiture et la présence d'images.</p>
        <?php endif; ?>
        <p style="text-align: center; margin-top: 3rem;"><a href="detail_voiture.php?id=<?php echo htmlspecialchars($id_voiture ?? ''); ?>" class="btn">Retour aux détails de la voiture</a></p>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Votre Entreprise de Location</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
