<?php
// Connexion à la base de données
$conn = new mysqli("127.0.0.1", "root", "", "ohada");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Tableau des tables à vider
$tables = [
    'journal_caisse',
    'journal_banque',
    'journal_fournisseurs',
    'journal_achats',
    'journal_ventes',
    'journal_clients' // Ajout de la table journal_clients
];

// Fonction pour vider les tables
function viderTable($conn, $table_name) {
    $sql = "DELETE FROM $table_name";
    if ($conn->query($sql) === TRUE) {
        echo "La table $table_name a été vidée avec succès.<br>";
    } else {
        echo "Erreur lors de la suppression des données dans la table $table_name : " . $conn->error . "<br>";
    }
}

// Vider les données de chaque table
foreach ($tables as $table_name) {
    viderTable($conn, $table_name);
}

$conn->close();
?>