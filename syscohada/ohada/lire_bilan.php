 <?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "comptabilite";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Requête SQL pour calculer le total des comptes actifs et passifs
$sql_actif = "SELECT SUM(montant_debit) - SUM(montant_credit) AS total_actif FROM operations_comptables WHERE compte_debit LIKE '1%'";
$sql_passif = "SELECT SUM(montant_credit) - SUM(montant_debit) AS total_passif FROM operations_comptables WHERE compte_credit LIKE '2%'";

// Afficher les requêtes SQL pour vérifier si elles sont correctes
echo "Requête SQL Actif : " . $sql_actif . "<br>";
echo "Requête SQL Passif : " . $sql_passif . "<br>";

// Exécution des requêtes
$result_actif = $conn->query($sql_actif);
$result_passif = $conn->query($sql_passif);

// Vérification si les résultats sont non nuls
if ($result_actif && $result_passif) {
    $total_actif = $result_actif->fetch_assoc()['total_actif'];
    $total_passif = $result_passif->fetch_assoc()['total_passif'];

    // Si les résultats sont NULL, les convertir à 0 pour éviter les erreurs
    $total_actif = isset($total_actif) ? $total_actif : 0;
    $total_passif = isset($total_passif) ? $total_passif : 0;
} else {
    echo "Erreur dans les requêtes SQL.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan Comptable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Bilan Comptable</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Montant (€)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Actif</td>
                <td><?php echo number_format($total_actif, 2); ?> €</td>
            </tr>
            <tr>
                <td>Total Passif</td>
                <td><?php echo number_format($total_passif, 2); ?> €</td>
            </tr>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "comptabilite";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Requête SQL pour calculer le total des comptes actifs et passifs
$sql_actif = "SELECT SUM(montant_debit) - SUM(montant_credit) AS total_actif FROM operations_comptables WHERE compte_debit LIKE '1%'";
$sql_passif = "SELECT SUM(montant_credit) - SUM(montant_debit) AS total_passif FROM operations_comptables WHERE compte_credit LIKE '2%'";

// Afficher les requêtes SQL pour vérifier si elles sont correctes
echo "Requête SQL Actif : " . $sql_actif . "<br>";
echo "Requête SQL Passif : " . $sql_passif . "<br>";

// Exécution des requêtes
$result_actif = $conn->query($sql_actif);
$result_passif = $conn->query($sql_passif);

// Vérification si les résultats sont non nuls
if ($result_actif && $result_passif) {
    $total_actif = $result_actif->fetch_assoc()['total_actif'];
    $total_passif = $result_passif->fetch_assoc()['total_passif'];

    // Si les résultats sont NULL, les convertir à 0 pour éviter les erreurs
    $total_actif = isset($total_actif) ? $total_actif : 0;
    $total_passif = isset($total_passif) ? $total_passif : 0;
} else {
    echo "Erreur dans les requêtes SQL.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan Comptable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Bilan Comptable</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Montant (€)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Actif</td>
                <td><?php echo number_format($total_actif, 2); ?> €</td>
            </tr>
            <tr>
                <td>Total Passif</td>
                <td><?php echo number_format($total_passif, 2); ?> €</td>
            </tr>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
