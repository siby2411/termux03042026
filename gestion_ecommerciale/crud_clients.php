<?php
// Fichier : crud_clients.php

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$conn = db_connect();

// --- LOGIQUE D'AFFICHAGE (READ) ---
$clients = [];
$result = $conn->query("SELECT id_client, nom, adresse, telephone FROM clients ORDER BY nom ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
}

$conn->close();

// Message de succès/erreur après une action CUD
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Clients</title>
</head>
<body>
    <h1>Gestion des Clients</h1>
    <p><a href="dashboard.php">Retour au Tableau de Bord</a></p>

    <?php if ($message): ?>
        <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h2>Ajouter un Nouveau Client</h2>
    <form action="traitement_client.php" method="post">
        <input type="hidden" name="action" value="ajouter">
        
        <label for="nom">Nom du Client:</label>
        <input type="text" name="nom" required><br><br>
        
        <label for="adresse">Adresse:</label>
        <input type="text" name="adresse"><br><br>
        
        <label for="telephone">Téléphone:</label>
        <input type="text" name="telephone"><br><br>
        
        <button type="submit">Ajouter Client</button>
    </form>

    <hr>
    
    <h2>Liste des Clients (<?php echo count($clients); ?>)</h2>
    <?php if (count($clients) > 0): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Téléphone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
                <td><?php echo $c['id_client']; ?></td>
                <td><?php echo htmlspecialchars($c['nom']); ?></td>
                <td><?php echo htmlspecialchars($c['adresse']); ?></td>
                <td><?php echo htmlspecialchars($c['telephone']); ?></td>
                <td>
                    <a href="modifier_client.php?id=<?php echo $c['id_client']; ?>">Modifier</a> |
                    <a href="traitement_client.php?action=supprimer&id=<?php echo $c['id_client']; ?>" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucun client enregistré pour le moment.</p>
    <?php endif; ?>
</body>
</html>
