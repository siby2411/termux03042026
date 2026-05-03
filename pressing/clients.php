<?php
require_once 'config.php';
$database = new Database();
$db = $database->getConnection();

$error = null;
$success = null;

// Vérification de la connexion avant toute opération
if (!$db) {
    $error = "Impossible de se connecter à MariaDB. Vérifiez que le service est lancé (service mariadb start).";
}

// Traitement de l'ajout
if ($db && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_client'])) {
    try {
        $stmt = $db->prepare("INSERT INTO clients (nom, prenom, telephone, email, adresse) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nom'], 
            $_POST['prenom'], 
            $_POST['telephone'], 
            $_POST['email'], 
            $_POST['adresse']
        ]);
        $success = "Client ajouté avec succès !";
    } catch (PDOException $e) {
        $error = "Erreur d'insertion : " . $e->getMessage();
    }
}

// Récupération sécurisée de la liste
$clients = [];
if ($db) {
    try {
        $clients = $db->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Erreur de lecture : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pressing Pro - Clients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .sidebar { background: #2c3e50; min-height: 100vh; color: white; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover { background: #34495e; color: white; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0 sidebar">
            <div class="p-3 text-center"><h4>👔 Pressing Pro</h4></div>
            <a href="dashboard.php"><i class="bi bi-house me-2"></i> Dashboard</a>
            <a href="clients.php" class="bg-primary text-white"><i class="bi bi-people me-2"></i> Clients</a>
            <a href="commandes.php"><i class="bi bi-box me-2"></i> Commandes</a>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Gestion des Clients</h2>

            <?php if ($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>
            <?php if ($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>

            <div class="card shadow-sm mb-5">
                <div class="card-header bg-white fw-bold">Ajouter un client</div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="nom" class="form-control" placeholder="Nom" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="prenom" class="form-control" placeholder="Prénom">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="telephone" class="form-control" placeholder="Téléphone">
                        </div>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" placeholder="Email">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="adresse" class="form-control" placeholder="Adresse">
                        </div>
                        <div class="col-12">
                            <button type="submit" name="ajouter_client" class="btn btn-primary">Ajouter le client</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Liste des clients</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th><th>Nom</th><th>Prénom</th><th>Téléphone</th><th>Email</th><th>Date inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['nom']) ?></td>
                                <td><?= htmlspecialchars($c['prenom']) ?></td>
                                <td><?= htmlspecialchars($c['telephone']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= $c['date_inscription'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
