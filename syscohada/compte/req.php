<?php
include 'config.php' ; 

 ?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Recherche de Comptes</title>
</head>
<body>
<div class="container mt-5">
    <h2>Recherche de Comptes</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="classe">Sélectionnez une Classe :</label>
            <select id="classe" name="classe" class="form-control" required>
                <option value="">Choisissez une classe</option>
                <?php
                include 'config.php'; // Inclure la configuration de la base de données

                // Récupérer les classes à partir de la base de données
                $stmt = $pdo->query("SELECT * FROM classe_comptes");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value=\"{$row['id']}\">{$row['nom']}</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['classe'])) {
        $classe_id = $_POST['classe'];

        // Préparer la requête pour sélectionner les comptes basés sur la classe
        $stmt = $pdo->prepare("SELECT c.numero_compte, c.nom 
                                FROM comptes c 
                                WHERE c.id_classe = ?");
        $stmt->execute([$classe_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Vérifier si des résultats ont été trouvés
        if ($result) {
            echo "<h2>Résultats de la recherche</h2>";
            echo "<table class=\"table table-bordered mt-3\">
                    <thead>
                        <tr>
                            <th>Numéro de Compte</th>
                            <th>Nom</th>
                        </tr>
                    </thead>
                    <tbody>";

            // Afficher les résultats dans le tableau
            foreach ($result as $row) {
                echo "<tr>
                        <td>{$row['numero_compte']}</td>
                        <td>{$row['nom']}</td>
                    </tr>";
            }

            echo "</tbody></table>";
        } else {
            echo "<h2>Aucun compte trouvé pour cette classe.</h2>";
        }
    }
    ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>