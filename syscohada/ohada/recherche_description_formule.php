<?php
// Connexion à la base de données MySQL
$servername = "127.0.0.1";
$username = "root";
$password = "123";
$dbname = "ohada";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// S'assurer que l'encodage est UTF-8
$conn->set_charset("utf8mb4");

if (isset($_POST['query'])) {
    // Récupérer la requête de recherche envoyée par l'utilisateur
    $searchQuery = $_POST['query'];

    // Préparer et exécuter la requête SQL pour chercher des formules
    $stmt = $conn->prepare("SELECT libelle, formule FROM formule_comptabilite WHERE libelle LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("s", $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    // Vérifier s'il y a des résultats et les afficher
    if ($result->num_rows > 0) {
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Libellé</th><th>Formule</th></tr></thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['libelle']) . '</td>';
            echo '<td>' . htmlspecialchars($row['formule']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Aucune formule trouvée.</p>';
    }

    $stmt->close();
}

// Fermer la connexion
$conn->close();
?>