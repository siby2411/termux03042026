<?php
// Configuration de la base de données
$host = '127.0.0.1'; // ou l'adresse IP de votre serveur de base de données
$dbname = 'ohada'; // Remplacez par le nom de votre base de données
$user = 'root'; // Remplacez par votre nom d'utilisateur de base de données
$password = '123'; // Remplacez par votre mot de passe de base de données

// Création d'une connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête pour récupérer les comptes OHADA
    $sql = "SELECT num_compte, intitule, description FROM comptes_ohada ORDER BY num_compte";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Récupération des résultats
    $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des comptes OHADA</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Liste des comptes OHADA</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Numéro de compte</th>
                    <th>Intitulé</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($comptes): ?>
                    <?php foreach ($comptes as $compte): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($compte['num_compte']); ?></td>
                            <td><?php echo htmlspecialchars($compte['intitule']); ?></td>
                            <td><?php echo htmlspecialchars($compte['description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">Aucun compte trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>