<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$id = intval($_GET['id'] ?? 0);

$query = "SELECT e.nom, e.prenom, e.code_etudiant, c.nom_class, c.montant_scolarite
          FROM etudiants e
          JOIN classes c ON e.classe_id = c.id
          WHERE e.id = $id";
$res = $conn->query($query);
$e = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification OMEGA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 text-center">
        <?php if($e): ?>
            <div class="card p-4 shadow">
                <h2 class="text-primary">Authentification OK</h2>
                <p>Étudiant : <strong><?= $e['nom'] . ' ' . $e['prenom'] ?></strong></p>
                <p>Classe : <strong><?= $e['nom_class'] ?></strong></p>
                <div class="alert alert-info">Code : <?= $e['code_etudiant'] ?></div>
                <hr>
                <a href="index.php" class="btn btn-dark">Retour à l'accueil</a>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">Carte invalide ou étudiant non trouvé.</div>
        <?php endif; ?>
    </div>
</body>
</html>
