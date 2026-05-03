<?php
// Connexion à la base de données MySQL
$servername = "127.0.0.1";
$username = "root";
$password = "123";
$dbname = "ohada";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Récupérer toutes les formules de la base de données
$query = "SELECT * FROM formule_comptabilite";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Formules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Liste des Formules Comptables</h1>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Libellé</th>
                    <th>Formule</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['libelle']; ?></td>
                        <td><?php echo $row['formule']; ?></td>
                        <td>
                            <!-- Boutons pour mettre à jour et supprimer -->
                            <a href="update_formule.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Mettre à jour</a>
                            <a href="delete_formule.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette formule ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Fermer la connexion
$conn->close();
?>