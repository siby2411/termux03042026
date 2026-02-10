<?php
include 'config.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Requête pour récupérer la liste des comptes
$stmt = $pdo->prepare("SELECT numero_compte, nom FROM comptes");
$stmt->execute();
$comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Comptes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Liste des Comptes</h2>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Numéro de Compte</th>
                    <th>Nom</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comptes as $compte): ?>
                    <tr>
                        <td><?= htmlspecialchars($compte['numero_compte']); ?></td>
                        <td><?= htmlspecialchars($compte['nom']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>