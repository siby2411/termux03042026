


<?php
// ... Début du header.php

// Vérification de l'authentification (AJOUTEZ OU VÉRIFIEZ CE BLOC)
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, le rediriger vers la page de connexion
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// ... Reste du code du header (définition de BASE_URL, inclusion de Bootstrap, etc.)




// --- Définition de la BASE_URL pour une navigation robuste ---
if (!defined('BASE_URL')) {
    // Calcule le chemin de base dynamique (ex: /banque/public)
    // SCRIPT_NAME est souvent /banque/public/dashboard.php
    $base_path = dirname($_SERVER['SCRIPT_NAME']);
    
    // Si header.php est appelé depuis un dossier parent (../includes/header.php), 
    // il faut remonter d'un niveau.
    // Cette méthode est plus fiable que de remplacer des chaînes.
    $BASE_URL_CALCULATED = str_replace('/includes', '', $base_path);
    
    // Assurez-vous d'avoir la bonne racine publique
    define('BASE_URL', $BASE_URL_CALCULATED); 
}

// ... Reste du code header.php ...
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mutuelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">



<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="dashboard.php">BANQUE Mutuelle</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    
    <div class="d-none d-md-inline-block ms-auto me-0 me-md-3 my-2 my-md-0">
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            
            <li class="nav-item">
                <a class="nav-link" href="analyse.php"><i class="fas fa-exchange-alt me-1"></i> Analyse (Dépôt/Retrait)</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="capitalisation.php"><i class="fas fa-calculator me-1"></i> Capitalisation</a>
            </li>


 <li class="nav-item">
                <a class="nav-link" href="annexe.html"><i class="fas fa-calculator me-1"></i> Annexe Documentation Logiciel</a>
            </li>



            
            <li class="nav-item">
                <a class="nav-link" href="analyse.php"><i class="fas fa-chart-line me-1"></i> Diagnostic Expert</a>
            </li>
            
            </ul>
    </div>
</nav>


















    
    </head>
<body>

