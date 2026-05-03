<?php
// Connexion à la base de données
$conn = new mysqli("127.0.0.1", "root", "", "ohada");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $element_bilan = $conn->real_escape_string($_POST['element_bilan']);
    $montant = floatval($_POST['montant']);

    // Vérifier si l'élément existe déjà pour mise à jour
    $sql = "SELECT * FROM bilan_ouverture WHERE element_bilan = '$element_bilan'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Mise à jour de l'élément existant
        $sql_update = "UPDATE bilan_ouverture SET montant = montant + $montant WHERE element_bilan = '$element_bilan'";
        if ($conn->query($sql_update) === TRUE) {
            echo "Élément mis à jour avec succès.";
        } else {
            echo "Erreur de mise à jour : " . $conn->error;
        }
    } else {
        // Insertion d'un nouvel élément
        $sql_insert = "INSERT INTO bilan_ouverture (element_bilan, montant) VALUES ('$element_bilan', $montant)";
        if ($conn->query($sql_insert) === TRUE) {
            echo "Élément ajouté avec succès.";
        } else {
            echo "Erreur d'insertion : " . $conn->error;
        }
    }
}

// Fermer la connexion
$conn->close();
?>