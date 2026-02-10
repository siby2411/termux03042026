<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet PME - Gestion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card-dashboard { transition: transform 0.2s; }
        .card-dashboard:hover { transform: translateY(-5px); shadow: lg; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="fas fa-network-wired"></i> Intranet PME</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Accueil</a></li>
        
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Services</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="comptabilite.php">Comptabilité & Factures</a></li>
            <li><a class="dropdown-item" href="marketing.php">Marketing</a></li>
            <li><a class="dropdown-item" href="logistique.php">Logistique & Stocks</a></li>
            <li><a class="dropdown-item" href="rh.php">Ressources Humaines</a></li>
          </ul>
        </li>

        <li class="nav-item"><a class="nav-link" href="documents.php">Documents</a></li>
      </ul>
      
      <div class="d-flex">
        <span class="navbar-text text-white me-3">Bonjour, Momo (Admin)</span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
      </div>
    </div>
  </div>
</nav>

<div class="container">
    ```

#### C. Le Pied de page (`includes/footer.php`)

```php
    </div>

<footer class="bg-light text-center text-lg-start mt-5 border-top">
  <div class="container p-4">
    <div class="row">
      <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
        <h5 class="text-uppercase">Intranet PME v1.0</h5>
        <p>Système de gestion intégré pour l'optimisation des flux entre services.</p>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
        <h5 class="text-uppercase">Liens Utiles</h5>
        <ul class="list-unstyled mb-0">
          <li><a href="#!" class="text-dark">Support IT</a></li>
          <li><a href="#!" class="text-dark">Procédures</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="text-center p-3 bg-dark text-white">
    © 2025 Copyright: Société d'Ingénierie Informatique
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
