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

// Vérifier que l'ID est fourni
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Préparer la requête de suppression
    $stmt = $conn->prepare("DELETE FROM formule_comptabilite WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Formule supprimée avec succès.";
    } else {
        echo "Erreur lors de la suppression de la formule : " . $stmt->error;
    }

    // Fermer la requête préparée
    $stmt->close();
}

// Rediriger vers la liste après suppression
header("Location: liste_formule.php");

// Fermer la connexion
$conn->close();
?>