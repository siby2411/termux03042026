<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>OMEGA ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Style pour la modale personnalisée */
        .upload-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; }
        .modal-content { background:white; width:400px; margin:100px auto; padding:20px; border-radius:8px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">OMEGA ERP</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Gestion Financière
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="paiement_scolarite.php"><i class="bi bi-wallet2"></i> Encaisser Scolarité</a></li>
                        <li><a class="dropdown-item" href="paiement_inscription.php"><i class="bi bi-pencil-square"></i> Encaisser Inscription</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="recherche_fiche.php"><i class="bi bi-search"></i> Recherche Fiche Suivi</a></li>
                        <li><a class="dropdown-item" href="etat_classe.php?classe_id=1"><i class="bi bi-graph-up"></i> État Financier</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="document.getElementById('upload-modal').style.display='block'">
                        <i class="bi bi-camera"></i> Mettre à jour Photo
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div id="upload-modal" class="upload-modal">
    <div class="modal-content">
        <h4 class="mb-3">Mise à jour Photo Étudiant</h4>
        <form action="upload_photo.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="number" name="etudiant_id" class="form-control" placeholder="ID Étudiant" required>
            </div>
            <div class="mb-3">
                <input type="file" name="photo" class="form-control" accept="image/jpeg" required>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('upload-modal').style.display='none'">Fermer</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
