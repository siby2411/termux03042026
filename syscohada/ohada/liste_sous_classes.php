<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'ohada'; // Remplacez par le nom de votre base de données
$username = 'root'; // Remplacez par votre utilisateur
$password = '123'; // Remplacez par votre mot de passe

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Requête pour récupérer les sous-classes OHADA
$query = "SELECT id, num_sous_classe, intitule_sous_classe FROM sous_classes_ohada";
$stmt = $pdo->prepare($query);
$stmt->execute();
$sous_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Sous-Classes OHADA</title>
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUa6m+FhIWFw8NNStSj8gpWh3IMqEwBtylk3aGtuw0wHbCd4J89cQGAk7hN" crossorigin="anonymous">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Liste des Sous-Classes OHADA</h2>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Numéro de la Sous-Classe</th>
                <th>Intitulé</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sous_classes)) : ?>
                <?php foreach ($sous_classes as $sous_classe) : ?>
                    <tr>
                        <td><?= htmlspecialchars($sous_classe['id']); ?></td>
                        <td><?= htmlspecialchars($sous_classe['num_sous_classe']); ?></td>
                        <td><?= htmlspecialchars($sous_classe['intitule_sous_classe']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3" class="text-center">Aucune sous-classe trouvée</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Lien vers Bootstrap JS et Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybBogGz1TxpL8Aw+lG4lHKybgI2vaOT38wGoFp/19dSL72AY" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93BdZ1sAB1iZ1iIJiA9YB9LZYfNrKfj7j6wKfqF5p4xMDJ+fanzNf5fF7Dx0z4" crossorigin="anonymous"></script>
</body>
</html>