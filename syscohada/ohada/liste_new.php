<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données MySQL
$host = 'localhost';
$dbname = 'ohada';
$username = 'root';
$password = '123';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête SQL pour obtenir les comptes OHADA avec les informations de sous-classe
    $sql = "
        SELECT comptes_ohada.id, comptes_ohada.num_compte, comptes_ohada.intitule, comptes_ohada.description, sous_classes_ohada.intitule_sous_classe
        FROM comptes_ohada
        LEFT JOIN sous_classes_ohada ON comptes_ohada.sous_classe_id = sous_classes_ohada.id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Comptes OHADA</title>
    <!-- Intégration de Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Liste des Comptes OHADA</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Numéro de Compte</th>
                    <th>Intitulé</th>
                    <th>Sous-classe</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($comptes)): ?>
                    <?php foreach ($comptes as $compte): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($compte['id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($compte['num_compte'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($compte['intitule'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($compte['intitule_sous_classe'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($compte['description'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucun compte OHADA trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Intégration de Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>