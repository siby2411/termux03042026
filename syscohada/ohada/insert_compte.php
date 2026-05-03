<?php
// Informations de connexion à la base de données
$servername = "127.0.0.1";
$username = "root";
$password = "123";
$dbname = "ohada";  // Remplacez par le nom de votre base de données

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $num_compte = $conn->real_escape_string($_POST['num_compte']);
    $intitule = $conn->real_escape_string($_POST['intitule']);
    $sous_classe_id = !empty($_POST['sous_classe_id']) ? (int) $_POST['sous_classe_id'] : NULL;
    $description = !empty($_POST['description']) ? $conn->real_escape_string($_POST['description']) : NULL;

    // Préparer la requête d'insertion
    $sql = "INSERT INTO comptes_ohada (num_compte, intitule, sous_classe_id, description) 
            VALUES ('$num_compte', '$intitule', ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $sous_classe_id, $description);

    // Exécuter la requête
    if ($stmt->execute()) {
        echo "Compte ajouté avec succès !";
    } else {
        echo "Erreur : " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

// Fermer la connexion
$conn->close();
?>