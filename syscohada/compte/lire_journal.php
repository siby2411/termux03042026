<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "comptabilite";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

$sql = "SELECT * FROM operations_comptables ORDER BY date_operation";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Comptable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Journal Comptable</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Compte Débit</th>
                <th>Compte Crédit</th>
                <th>Montant</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['date_operation']}</td>
                            <td>{$row['compte_debit']}</td>
                            <td>{$row['compte_credit']}</td>
                            <td>{$row['montant_debit']}</td>
                            <td>{$row['description']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Aucune opération trouvée</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</
