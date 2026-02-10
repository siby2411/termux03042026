<?php
// Fichier : modifier_client.php

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$id_client = intval($_GET['id'] ?? 0);

if ($id_client === 0) {
    header("Location: crud_clients.php");
    exit();
}

$conn = db_connect();

// Récupérer les données du client
$sql = "SELECT id_client, nom, adresse, telephone FROM clients WHERE id_client = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_client);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    $_SESSION['message'] = "Erreur: Client non trouvé.";
    header("Location: crud_clients.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Client: <?php echo htmlspecialchars($client['nom']); ?></title>
</head>
<body>
    <h1>Modifier le Client</h1>
    <p><a href="crud_clients.php">Retour à la Liste des Clients</a></p>

    <h2>Client: <?php echo htmlspecialchars($client['nom']); ?></h2>
    
    <form action="traitement_client.php" method="post">
        <input type="hidden" name="action" value="modifier">
        <input type="hidden" name="id_client" value="<?php echo $client['id_client']; ?>">
        
        <label for="nom">Nom du Client:</label>
        <input type="text" name="nom" value="<?php echo htmlspecialchars($client['nom']); ?>" required><br><br>
        
        <label for="adresse">Adresse:</label>
        <input type="text" name="adresse" value="<?php echo htmlspecialchars($client['adresse']); ?>"><br><br>
        
        <label for="telephone">Téléphone:</label>
        <input type="text" name="telephone" value="<?php echo htmlspecialchars($client['telephone']); ?>"><br><br>
        
        <button type="submit">Enregistrer les Modifications</button>
    </form>
</body>
</html>



