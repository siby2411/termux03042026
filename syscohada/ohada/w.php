<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Cours</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Modifier un Cours</h2>
        
        <?php
        // Connexion à la base de données MySQL
        $host = '127.0.0.1';
        $dbname = 'ohada';
        $user = 'root';
        $password = '123';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Vérifier si un formulaire a été soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Récupérer les valeurs du formulaire
                $id = $_POST['cours_id'];
                $nouvel_intitule = $_POST['nouvel_intitule'];

                // Mise à jour du cours dans la base de données
                $sql = "UPDATE cours SET intitulé = :nouvel_intitule WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nouvel_intitule', $nouvel_intitule, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>L'intitulé du cours a été modifié avec succès.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Erreur lors de la modification du cours.</div>";
                }
            }

            // Récupérer tous les cours pour les afficher dans le formulaire de sélection
            $sql = "SELECT id, intitulé FROM cours ORDER BY intitulé ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        ?>

        <form action="update_cours.php" method="POST">
            <div class="form-group">
                <label for="cours">Sélectionnez un cours à modifier</label>
                <select class="form-control" id="cours" name="cours_id" required>
                    <option value="">-- Sélectionnez un cours --</option>
                    <?php
                    // Afficher les options de cours dans la liste déroulante
                    foreach ($cours as $row) {
                        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['intitulé']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="nouvel_intitule">Nouveau intitulé du cours</label>
                <input type="text" class="form-control" id="nouvel_intitule" name="nouvel_intitule" placeholder="Entrez le nouvel intitulé" required>
            </div>
            <button type="submit" class="btn btn-primary">Modifier</button>
        </form>

        <!-- Ajout des boutons de redirection -->
        <div class="mt-3">
            <!-- Bouton pour rediriger vers req_cours.php -->
            <button onclick="window.location.href='reqcours.php'" class="btn btn-secondary">Requête Cours</button>

            <!-- Bouton pour rediriger vers ajout_cours.php -->
            <button onclick="window.location.href='cours.html'" class="btn btn-success ml-2">Ajouter un Cours</button>
        </div>
    </div>
</body>
</html>