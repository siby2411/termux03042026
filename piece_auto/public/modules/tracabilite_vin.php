<?php
// /var/www/piece_auto/public/modules/tracabilite_vin.php
include_once '../../config/Database.php'; 
include '../../includes/header.php'; 

$page_title = "Traçabilité par VIN (Numéro d'Identification du Véhicule)";
// Aucune requête BDD ici, car c'est un formulaire de recherche
?>
<div class="container-fluid">
    <h1><i class="fas fa-id-card"></i> <?= $page_title ?></h1>
    <p class="lead">Rechercher l'historique des pièces vendues ou installées pour un véhicule spécifique.</p>
    
    <div class="card p-4">
        <form method="GET" action="">
            <div class="input-group">
                <input type="text" name="vin" class="form-control" placeholder="Entrez le numéro VIN (17 caractères)">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
            </div>
        </form>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>
