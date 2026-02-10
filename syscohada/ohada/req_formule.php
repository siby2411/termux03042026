<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données MySQL
$servername = "localhost";
$username = "root";
$password = "123";
$dbname = "ohada";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Initialiser la variable pour stocker les résultats de la recherche
$resultats = [];

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer le libellé recherché
    $libelle_recherche = $conn->real_escape_string(trim($_POST['libelle']));

    // Préparer la requête de recherche
    $stmt = $conn->prepare("SELECT * FROM formule_comptabilite WHERE libelle LIKE ?");
    $like_libelle = "%" . $libelle_recherche . "%";
    $stmt->bind_param("s", $like_libelle);

    // Exécuter la requête
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        // Récupérer les résultats
        while ($row = $result->fetch_assoc()) {
            $resultats[] = $row;
        }
    } else {
        echo "Erreur lors de la recherche : " . $stmt->error;
    }

    // Fermer la requête préparée
    $stmt->close();
}

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Formules</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Recherche de Formules Comptables</h2>
    <form action="" method="POST">
        <div class="form-group">
            <label for="libelle">Libellé :</label>
            <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Entrez un libellé" required>
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    <?php if (!empty($resultats)): ?>
        <h3 class="mt-4">Résultats de la recherche :</h3>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Libellé</th>
                <th>Formule</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($resultats as $formule): ?>
                <tr>
                    <td><?php echo htmlspecialchars($formule['id']); ?></td>
                    <td><?php echo htmlspecialchars($formule['libelle']); ?></td>
                    <td><?php echo htmlspecialchars($formule['formule']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <div class="alert alert-warning">Aucune formule trouvée pour le libellé "<?= htmlspecialchars($libelle_recherche) ?>".</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>