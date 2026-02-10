<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "123", "ohada");

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupération des données du formulaire
$date_operation = $_POST['date_operation'];
$description = $_POST['description'];
$montant = $_POST['montant'];
$numero_compte = $_POST['numero_compte'];
$statut = $_POST['statut'];

// Requête d'insertion
$sql = "INSERT INTO operation_diverse (date_operation, description, montant, numero_compte, statut) 
        VALUES (?, ?, ?, ?, ?)";

// Préparation de la requête
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Erreur de préparation de la requête : " . $conn->error);
}

// Liaison des paramètres
$stmt->bind_param("ssdss", $date_operation, $description, $montant, $numero_compte, $statut);

// Exécution de la requête
if ($stmt->execute()) {
    echo "Opération diverse ajoutée avec succès !";
} else {
    echo "Erreur lors de l'insertion : " . $stmt->error;
}

// Fermeture de la connexion
$stmt->close();
$conn->close();
?>