<?php
// Informations de connexion à la base de données
$host = 'localhost'; // Nom d'hôte du serveur MySQL
$dbname = 'ohada'; // Nom de la base de données
$username = ''; // Nom d'utilisateur MySQL
$password = 'votre_mot_de_passe'; // Mot de passe MySQL

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête SQL pour sélectionner toutes les classes OHADA
    $sql = "SELECT * FROM classes_ohada";
    $stmt = $pdo->query($sql);

    // Affichage des données dans un tableau HTML
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Numéro de Classe</th><th>Intitulé</th><th>Statut</th></tr>";

    // Boucle pour récupérer chaque ligne du résultat et l'afficher
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['num_classe']) . "</td>";
        echo "<td>" . htmlspecialchars($row['intitule_classe']) . "</td>";
        echo "<td>" . htmlspecialchars($row['statut']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}

?>