<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'secretariat' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$message = '';

// --- TRAITEMENT DU FORMULAIRE D'UPLOAD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fichier'])) {
    $titre = htmlspecialchars($_POST['titre']);
    $nom_original = $_FILES['fichier']['name']; 
    $nom_unique = uniqid() . '_' . basename($nom_original);
    $chemin_relatif = 'uploads/documents/' . $nom_unique;
    
    // ID de l'utilisateur connecté
    $expediteur_id = $_SESSION['user_id'];
    
    if (move_uploaded_file($_FILES['fichier']['tmp_name'], '../../' . $chemin_relatif)) {
        // Ajout de 'expediteur_id' dans la requête
        $stmt = $pdo->prepare("INSERT INTO documents (titre, fichier_nom, fichier_chemin, expediteur_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt->execute([$titre, $nom_original, $chemin_relatif, $expediteur_id])) {
            $message = "Document envoyé avec succès !";
        } else {
            $message = "Erreur SQL lors de l'insertion.";
        }
    } else {
        $message = "Erreur lors du déplacement du fichier.";
    }
}

$stmt = $pdo->query("SELECT * FROM documents ORDER BY created_at DESC");
$documents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container-fluid p-4">
    <h2>Gestion des documents</h2>
    <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <form method="POST" enctype="multipart/form-data" class="card p-3 shadow-sm">
                <div class="mb-3"><label>Titre</label><input type="text" name="titre" class="form-control" required></div>
                <div class="mb-3"><label>Fichier</label><input type="file" name="fichier" class="form-control" required></div>
                <button type="submit" class="btn btn-primary w-100">Uploader</button>
            </form>
        </div>
        <div class="col-md-8">
            <div class="card p-3 shadow-sm">
                <table class="table table-hover">
                    <thead><tr><th>Titre</th><th>Fichier</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['titre']) ?></td>
                            <td><?= htmlspecialchars($doc['fichier_nom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($doc['created_at'])) ?></td>
                            <td><a href="/<?= htmlspecialchars($doc['fichier_chemin']) ?>" class="btn btn-sm btn-primary" download><i class="fas fa-download"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
