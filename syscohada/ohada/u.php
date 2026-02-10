<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omega Informatique Recherche OHADA</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Couleur d'arrière-plan légèrement grisée */
        }
        .header-image {
            width: 100%;
            height: auto;
            max-height: 300px; /* Réduction de la hauteur maximale à 300px */
            object-fit: cover;
        }
        .logo {
            max-width: 150px; /* Largeur maximale pour le logo */
            margin-bottom: 20px; /* Espace sous le logo */
        }
    </style>
</head>
<body>
    <!-- Logo Omega Informatique en haut de la page -->
    <div class="container text-center mt-3">
        <img src="omega informatique 1.jpg" alt="Omega Informatique" class="logo">
    </div>

    <!-- Image en haut de la page -->
    <div class="container mt-3">
        <img src="4.jpg" alt="Omega Informatique" class="img-fluid header-image">
    </div>

    <div class="container mt-5">
        <h2 class="text-center">Omega Informatique Recherche OHADA</h2>

        <form action="ajout_cours.php" method="POST">
            <div class="form-group">
                <label for="intitule">Intitulé du Cours</label>
                <input type="text" class="form-control" id="intitule" name="intitule" required>
            </div>
            <div class="form-group">
                <label for="cours">Contenu du Cours</label>
                <textarea class="form-control" id="cours" name="cours" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>

        <!-- Ajout des boutons de redirection -->
        <div class="mt-3">
            <button onclick="window.location.href='update_cours.php'" class="btn btn-secondary">Modifier un Cours</button>
            <button onclick="window.location.href='reqcours.php'" class="btn btn-info ml-2">Requête Cours</button>
        </div>
    </div>

    <!-- Intégration de Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>