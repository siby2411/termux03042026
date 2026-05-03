<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Cours</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Rechercher un Cours</h2>
        <form action="recherche_cours.php" method="POST">
            <div class="form-group">
                <label for="cours">Sélectionnez un cours</label>
                <select class="form-control" id="cours" name="cours_id" required>
                    <option value="">-- Sélectionnez un cours --</option>
                    <?php
                    // Connexion à la base de données mySQL
                    $host = '127.0.0.1';
                    $dbname = 'ohada';
                    $user = 'root';
                    $password = '123';

                    try {
                        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        // Requête pour récupérer les intitulés des cours
                        $sql = "SELECT id, intitulé FROM cours";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();

                        // Boucle pour afficher les options du select
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['id'] . "'>" . $row['intitulé'] . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "Erreur : " . $e->getMessage();
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </div>
</body>
</html>