

<?php include('config.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OHADA Navigation Menu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navigation Menu -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">OHADA System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="listeglobale.php">liste globale Compte</a>
        </li>


<li class="nav-item">
          <a class="nav-link" href="cours.html"> ajout cours </a>
        </li>   

<li class="nav-item">
          <a class="nav-link" href="ajax.php"> recherche cours </a>
        </li>


<li class="nav-item">
          <a class="nav-link" href="reqcours.php"> recherche cours </a>
        </li>



        <li class="nav-item">
          <a class="nav-link" href="facture.php">Gestion Factures</a>
        </li>
     
   <li class="nav-item">
          <a class="nav-link" href="liste_classes.php">Liste des Classes</a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link" href="export1.php">Exporter sous excel</a>
        </li>




   <li class="nav-item">
          <a class="nav-link" href="liste_compte.php">Liste des Comptes OHADA </a>
        </li>


  <li class="nav-item">
          <a class="nav-link" href="facture.php">Écriture Comptable </a>
        </li>
        
        
        
        <li class="nav-item">
          <a class="nav-link" href="liste_new.php">Nouvelle liste compte </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link" href="liste_excel.php"> liste excel compte </a>
        </li>



 
   <li class="nav-item">
          <a class="nav-link" href="search.php">Ajax recherche numéro compte OK</a>
        </li>

<li class="nav-item">
          <a class="nav-link" href="operation.php">Ajouter une Écriture Comptable </a>
        </li>


<li class="nav-item">
          <a class="nav-link" href="ecriture_ajax.html">Ajouter une Écriture Comptable Ajax</a>
        </li>


<li class="nav-item">
          <a class="nav-link" href="liste_ecriture.php">Liste Écriture Comptable </a>
        </li>

<li class="nav-item">
          <a class="nav-link" href="bal3.php">Balance Comptable </a>
        </li>


<li class="nav-item">
          <a class="nav-link" href="reqdescript.php">Recherche numéro compte par Description </a>
        </li>



<li class="nav-item">
          <a class="nav-link" href="reqnumero.php"> recherche numéro compte </a>
        </li>

<li class="nav-item">
          <a class="nav-link" href="req1.php">Ajax recherche numéro compte </a>
        </li>


<li class="nav-item">
          <a class="nav-link" href="ajout_formule.html">Ajout Formule comptable </a>
        </li>

<li class="nav-item">
          <a class="nav-link" href="liste_formule.php">Liste Formule comptable </a>
        </li>

<li class="nav-item">
          <a class="nav-link" href="req_formule.php">Recherche Formule comptable </a>
        </li>


<li class="nav-item">
          <a class="nav-link" href="sig.php">Calcul SIG</a>
        </li>
      
      
      
   <li class="nav-item">
          <a class="nav-link" href="journal.html">Journal Auxiliaire </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link" href="liste_journaux.php">Liste Journaux </a>
        </li>
        
     
        
        
        <li class="nav-item">
          <a class="nav-link" href="add_bilan.html">Bilan Ouverture </a>
        </li>


        <li class="nav-item">
          <a class="nav-link" href="bilanouverture.php">Bilan Ouverture </a>
        </li>

        
        
        <li class="nav-item">
          <a class="nav-link" href="op.html">opération diverse </a>
        </li>


<li class="nav-item">
          <a class="nav-link" href="listeop.php">liste opération diverse </a>
        </li>



<li class="nav-item">
          <a class="nav-link" href="bf3.php">bilan final avec opération diverse </a>
        </li>

<li class="nav-item">
          <a class="nav-link" href="deljournal.php"> Supprimer Journal </a>
        </li>   
<li class="nav-item">
          <a class="nav-link" href="cours.html"> ajout cours </a>
        </li>   

<li class="nav-item">
          <a class="nav-link" href="reqcours.php"> recherche cours </a>
        </li>   


 
      

        <li class="nav-item">
          <a class="nav-link" href="liste_sous_classes.php">Liste des Sous-Classes</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Bootstrap JS (necessary for navbar functionality) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>