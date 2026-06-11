<?php 
require_once '../../includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pharmacie - Centre Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="d-flex">
    <div style="width: 250px;"><?php include '../../includes/sidebar.php'; ?></div>
    <div class="flex-grow-1 p-4">
        <h1><i class="fas fa-pills"></i> Gestion de la Pharmacie</h1>
        <hr>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card p-4 bg-light">
                    <h3>Stock Médicaments</h3>
                    <p>Visualiser l'état des stocks actuels.</p>
                    <a href="#" class="btn btn-primary">Voir le stock</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 bg-light">
                    <h3>Délivrance</h3>
                    <p>Enregistrer une nouvelle sortie.</p>
                    <a href="#" class="btn btn-success">Délivrer</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 bg-light">
                    <h3>Approvisionnement</h3>
                    <p>Ajouter des arrivages.</p>
                    <a href="#" class="btn btn-warning">Ajouter</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
