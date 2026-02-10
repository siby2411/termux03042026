<?php
if (isset($_POST['num_compte'])) {
    // Connexion à la base de données
    $conn = new mysqli("localhost", "root", "123", "ohada");

    // Vérifiez la connexion
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Récupérer le numéro de compte soumis
    $num_compte = $conn->real_escape_string($_POST['num_compte']);

    // Requête pour vérifier si le compte existe
    $sql = "SELECT * FROM comptes_ohada WHERE num_compte = '$num_compte'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Si le compte existe
        $row = $result->fetch_assoc();
        echo "<p>Numéro de compte : " . $row['num_compte'] . "</p>";
        echo "<p>Intitulé : " . $row['intitule'] . "</p>";
        echo "<p>Description : " . $row['description'] . "</p>";
    } else {
        // Si le compte n'existe pas
        echo "<p>Le numéro de compte n'existe pas.</p>";
    }

    // Fermer la connexion
    $conn->close();
}
?>