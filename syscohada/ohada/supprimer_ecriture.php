<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "123";
$dbname = "ohada";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Requête pour récupérer toutes les écritures
$sql = "SELECT * FROM ecritures";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Liste des Écritures</title>
    <style>
        /* Style pour distinguer les colonnes débit et crédit */
        .debit {
            background-color: #f8d7da; /* Couleur de fond pour débit */
        }
        .credit {
            background-color: #d4edda; /* Couleur de fond pour crédit */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Liste des Écritures Comptables</h2>
    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Date Opération</th>
            <th>Compte Débit</th>
            <th>Intitulé Débit</th>
            <th>Montant Débit</th>
            <th>Compte Crédit</th>
            <th>Intitulé Crédit</th>
            <th>Montant Crédit</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['date_operation'] . "</td>";
                echo "<td class='debit'>" . $row['compte_debit'] . "</td>";
                echo "<td class='debit'>" . $row['intitule_debit'] . "</td>";
                echo "<td class='debit'>" . $row['debit'] . "</td>"; // Montant Débit
                echo "<td class='credit'>" . $row['compte_credit'] . "</td>";
                echo "<td class='credit'>" . $row['intitule_credit'] . "</td>";
                echo "<td class='credit'>" . $row['credit'] . "</td>"; // Montant Crédit
                echo "<td>" . $row['description'] . "</td>";
                // Ajouter le bouton Supprimer
                echo "<td>
                        <a href='modifier_ecriture.php?id=" . $row['id'] . "' class='btn btn-primary'>Modifier</a>
                        <a href='supprimer_ecriture.php?id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cette écriture ?\");'>Supprimer</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='10'>Aucune écriture trouvée</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
$conn->close();
?>