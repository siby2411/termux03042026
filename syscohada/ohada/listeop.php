<?php
// Connexion à la base de données
$conn = new mysqli("127.0.0.1", "root", "", "ohada");

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Requête pour récupérer les opérations diverses
$sql = "SELECT * FROM operation_diverse";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Opérations Diverses</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Liste des Opérations Diverses</h2>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date de l'Opération</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Numéro de Compte</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= $row['date_operation']; ?></td>
                            <td><?= $row['description']; ?></td>
                            <td><?= number_format($row['montant'], 2); ?></td>
                            <td><?= $row['numero_compte']; ?></td>
                            <td><?= $row['statut']; ?></td>
                            <td>
                                <a href="update_operation_diverse.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">Modifier</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">Aucune opération diverse trouvée.</p>
        <?php endif; ?>

        <a href="op.html" class="btn btn-success mt-3">Ajouter une Nouvelle Opération</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>