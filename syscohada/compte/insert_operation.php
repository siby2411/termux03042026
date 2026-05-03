<?php
// Connexion à la base de données MySQL
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "comptabilite";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupération des données du formulaire
$compte_debit = $_POST['compte_debit'];
$compte_credit = $_POST['compte_credit'];
$montant = $_POST['montant'];
$description = $_POST['description'];
$date_operation = date('Y-m-d');

// Insertion de l'opération dans la base de données
$sql = "INSERT INTO operations_comptables (date_operation, compte_debit, compte_credit, montant_debit, montant_credit, description)
        VALUES ('$date_operation', '$compte_debit', '$compte_credit', '$montant', '$montant', '$description')";

if ($conn->query($sql) === TRUE) {
    echo "Nouvelle opération ajoutée avec succès";
} else {
    echo "Erreur : " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
