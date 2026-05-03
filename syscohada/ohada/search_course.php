<?php
// Connexion à la base de données
$host = '127.0.0.1';
$dbname = 'ohada';
$user = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifie si une requête de recherche est envoyée
    if (isset($_POST['query'])) {
        $query = $_POST['query'];
        $sql = "SELECT id, intitulé FROM cours WHERE intitulé LIKE :query LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['query' => $query . '%']);

        // Affiche les résultats
        if ($stmt->rowCount() > 0) {
            echo "<ul class='list-group mt-2'>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<li class='list-group-item'><a href='#' class='course-link' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['intitulé']) . "</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='text-warning'>Aucun résultat trouvé.</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p class='text-danger'>Erreur : " . $e->getMessage() . "</p>";
}
?>