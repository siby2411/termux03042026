<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données MySQL
$servername = "127.0.0.1";
$username = "root"; // Remplacez par votre nom d'utilisateur
$password = "123"; // Remplacez par votre mot de passe
$dbname = "ohada"; // Remplacez par le nom de votre base de données

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Ajouter une entrée au journal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $date_operation = $_POST['date_operation'];
    $description = $_POST['description'];
    $montant = $_POST['montant'];

    // Préparer la requête d'insertion
    $stmt = $conn->prepare("INSERT INTO journal_ventes (date_operation, description, montant) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $date_operation, $description, $montant);
    $stmt->execute();
    $stmt->close();
}

// Supprimer une entrée
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $conn->prepare("DELETE FROM journal_ventes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Récupérer les données du journal
$result = $conn->query("SELECT * FROM journal_ventes ORDER BY date_operation DESC");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Journal des Ventes</title>
</head>
<body>
    <h1>Journal des Ventes</h1>
    
    <form method="POST" action="">
        <label for="date_operation">Date :</label>
        <input type="date" name="date_operation" required>
        <label for="description">Description :</label>
        <input type="text" name="description" required>
        <label for="montant">Montant :</label>
        <input type="number" step="0.01" name="montant" required>
        <input type="submit" name="ajouter" value="Ajouter">
    </form>

    <h2>Liste des Ventes</h2>
    <table border="1">
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Montant</th>
            <th>Actions</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['date_operation']; ?></td>
                    <td><?php echo $row['description']; ?></td>
                    <td><?php echo $row['montant']; ?></td>
                    <td><a href="?supprimer=<?php echo $row['id']; ?>">Supprimer</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Aucune donnée disponible</td></tr>
        <?php endif; ?>
    </table>
    
</body>
</html>

<?php
// Fermer la connexion
$conn->close();
?>