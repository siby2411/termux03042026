<?php
// Connexion à la base de données MySQL
$servername = "localhost";
$username = "root"; // Changez selon votre configuration
$password = "123"; // Changez selon votre configuration
$dbname = "ohada"; // Remplacez par le nom de votre base de données

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Requête pour récupérer la liste des classes
$sql = "SELECT num_classe, intitule_classe, statut FROM classes_ohada";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Classes OHADA</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Liste des Classes OHADA</h2>
    
    <!-- Table pour afficher les classes -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Numéro de Classe</th>
                <th>Intitulé de la Classe</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Vérifier si des résultats sont trouvés
            if ($result->num_rows > 0) {
                // Parcours des résultats et affichage dans le tableau
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['num_classe']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['intitule_classe']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['statut']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>Aucune classe trouvée.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Fermeture de la connexion -->
<?php
$conn->close();
?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>