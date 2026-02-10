<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Cours</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
        }
        .logo {
            display: block;
            margin: 20px auto; /* Centre le logo horizontalement */
            max-width: 150px; /* Taille maximale du logo */
            height: auto;
        }
    </style>
</head>
<body>
    <!-- Logo Omega Informatique -->
    <div class="container text-center">
        <img src="4.jpg" alt="Omega Informatique" class="logo">
    </div>

    <div class="container mt-5">
        <h2 class="text-center">Rechercher un Module Syscohada </h2>
        
        <form action="recherche_cours.php" method="POST">
            <div class="form-group">
                <label for="cours">Sélectionnez un module </label>
                <select class="form-control" id="cours" name="cours_id" required>
                    <option value="">-- Sélectionnez un module --</option>
                    <?php
                    // Connexion à la base de données MySQL
                    $host = 'localhost';
                    $dbname = 'ohada';
                    $user = 'root';
                    $password = '123';

                    try {
                        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        // Requête pour récupérer les intitulés des cours et les trier par ordre alphabétique
                        $sql = "SELECT id, intitulé FROM cours ORDER BY intitulé ASC";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();

                        // Boucle pour afficher les options du select
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['intitulé']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
        
        <!-- Boutons pour modifier et ajouter un cours -->
        <div class="mt-3">
            <button onclick="window.location.href='update_cours.php'" class="btn btn-warning">Modifier un Cours</button>
            <button onclick="window.location.href='cours.html'" class="btn btn-success">Ajouter un Cours</button>
        </div>
    </div>

    <!-- Intégration de Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>