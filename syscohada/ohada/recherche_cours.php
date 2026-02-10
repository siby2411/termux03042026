<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omega Informatique Recherche Syscohada  - Détails du Module </title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .header-image {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Image d'en-tête -->
    <div class="container mt-3">
        <img src="4.jpg" alt="Omega Informatique" class="img-fluid header-image">
    </div>

    <div class="container mt-5">
        <h2 class="text-center">Omega Informatique 
<br>
Recherche SYSCOHADA 

<br>

 Détails du Module
 </h2>

        <?php
        // Connexion à la base de données PostgreSQL
        $host = 'localhost';
        $dbname = 'ohada';
        $user = 'root';
        $password = '123';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Vérifier si un cours a été sélectionné
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cours_id'])) {
                $cours_id = $_POST['cours_id'];

                // Requête pour récupérer les détails du cours sélectionné
                $sql = "SELECT intitulé, cours FROM cours WHERE id = :cours_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':cours_id', $cours_id, PDO::PARAM_INT);
                $stmt->execute();

                // Afficher les résultats
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<div class='mt-4'>";
                    echo "<h3>Intitulé : " . htmlspecialchars($row['intitulé']) . "</h3>";
                    echo "<p><strong>Contenu du cours :</strong><br>" . nl2br(htmlspecialchars($row['cours'])) . "</p>";
                    echo "</div>";
                } else {
                    echo "<p class='text-danger'>Aucun cours trouvé pour cet ID.</p>";
                }
            } else {
                echo "<p class='text-warning'>Veuillez sélectionner un cours.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='text-danger'>Erreur : " . $e->getMessage() . "</p>";
        }
        ?>
    </div>

    <!-- Intégration de Bootstrap JS pour fonctionnalités Bootstrap (comme modales) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>