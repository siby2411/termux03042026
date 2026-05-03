<?php
// Note : session_start() doit être appelé DANS la page spécifique (ex: dashboard.php)
// AVANT d'inclure ce header, si vous en avez besoin.
// Exemple :
// <?php session_start(); include 'header.php'; ?>
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Commerciale - Moderne</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
          
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">E-Commerciale</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="crud_produits.php">Produits</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="crud_clients.php">Clients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="facturation.php">Facturation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rapports_ventes.php">Rapports</a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="btn btn-outline-danger" href="logout.php">Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4 mb-5">
