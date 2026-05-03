<?php
// /includes/header.php
// Assure l'ouverture du document HTML, inclut les CSS et ouvre le conteneur principal.

// S'assurer que $page_title est défini
if (!isset($page_title)) {
    $page_title = "Gestion Prévisionnelle";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Gestion Prévisionnelle</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* CSS simple pour assurer que le footer soit en bas */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            flex: 1;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="/gestion_previsionnelle/dashboard_pilote.php">
            <i class="fas fa-cubes me-2"></i> Gestion Prévisionnelle
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/dashboard_pilote.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/clients/index.php"><i class="fas fa-users"></i> Clients</a></li>
            <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/produits/index.php"><i class="fas fa-boxes"></i> Produits</a></li>
            <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/commandes/index.php"><i class="fas fa-shopping-cart"></i> Commandes</a></li>

            <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/rapport_synthese.php"><i class="fas fa-file-invoice-dollar"></i> Rapports</a></li>

 <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/fournisseurs/index.php"><i class="fas fa-file-invoice-dollar"></i> Fournisseurs</a></li>
   

  <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/segmentation_client.php"><i class="fas fa-file-invoice-dollar"></i> Segmentation_Client</a></li>
       
  <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/form_ecriture.php"><i class="fas fa-file-invoice-dollar"></i> ecriture Comptable</a></li>
   
  <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/tableau_de_bord_complet.php"><i class="fas fa-file-invoice-dollar"></i>tableau bord_complet</a></li>
   
  <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/compte_de_resultat_formel.php"><i class="fas fa-file-invoice-dollar"></i> compte_de_resultat</a></li>
   
  <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/bilan_formel.php"><i class="fas fa-file-invoice-dollar"></i> bilan_formel  </a></li>
   
  <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/analyse_structurelle.php"><i class="fas fa-file-invoice-dollar"></i>analyse_structurelle </a></li>
   
  <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/list_grandlivre.php"><i class="fas fa-file-invoice-dollar"></i>  grand livre </a></li>
   
  <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/point_mort.php"><i class="fas fa-file-invoice-dollar"></i>point_mort</a></li>
   
  <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/etats_financiers.php"><i class="fas fa-file-invoice-dollar"></i> etats_financiers </a></li>
   
<li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/analyse_detaillee.php"><i class="fas fa-file-invoice-dollar"></i>analyse_detaillee  </a></li>
 <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/achats/index.php"><i class="fas fa-file-invoice-dollar"></i> Achats</a></li>
 <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/form_budget.php"><i class="fas fa-file-invoice-dollar"></i>Gestion Budget</a></li>

 <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/form_flux.php"><i class="fas fa-file-invoice-dollar"></i>flux tresorerie</a></li>
       


 <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/form_compte.php"><i class="fas fa-file-invoice-dollar"></i>Ajouter compte</a></li>
       

 <li class="nav-item"><a class="nav-link" href="/gestion_previsionnelle/annexe.html"><i class="fas fa-file-invoice-dollar"></i>Annexe</a></li>
       




  
          </ul>
        </div>
      </div>
    </nav>
    
    <div class="container mt-4 mb-4">
