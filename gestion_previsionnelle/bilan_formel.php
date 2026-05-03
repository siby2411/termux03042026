<?php
// /bilan_formel.php
$page_title = "Bilan Annuel Complet";
// IMPORTANT: Inclure ici le bloc de calcul complet du tableau_de_bord_complet.php
include_once 'tableau_de_bord_calcul.php'; // On suppose que le bloc de calcul est dans un fichier séparé
include_once 'includes/header.php'; 
?>

<div class="container my-5">
    <h1 class="mb-4 text-dark text-center"><i class="fas fa-balance-scale me-2"></i> Bilan Annuel au <?= date('Y-m-d') ?></h1>
    <p class="text-muted text-center">Structure des emplois et ressources de l'entreprise.</p>
    <hr>
    
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-secondary h-100">
                <div class="card-header bg-secondary text-white fw-bold">ACTIF (Emplois)</div>
                <div class="card-body">
                    <table class="table table-sm table-striped">
                        <thead class="table-light"><tr><th colspan="2">Actif Immobilisé</th></tr></thead>
                        <tr><th>Immobilisations (Net)</th><td class="text-end"><?= number_format($bilan_actif['Immobilisations (Net)'], 2, ',', ' ') ?> €</td></tr>
                        
                        <thead class="table-light"><tr><th colspan="2">Actif Circulant</th></tr></thead>
                        <tr><th>Stocks</th><td class="text-end"><?= number_format($bilan_actif['Stocks'], 2, ',', ' ') ?> €</td></tr>
                        <tr><th>Créances Clients</th><td class="text-end"><?= number_format($bilan_actif['Créances Clients'], 2, ',', ' ') ?> €</td></tr>
                        
                        <thead class="table-light"><tr><th colspan="2">Trésorerie Actif</th></tr></thead>
                        <tr><th>Trésorerie / Banque</th><td class="text-end"><?= number_format($bilan_actif['Trésorerie / Banque'], 2, ',', ' ') ?> €</td></tr>
                        
                        <tr class="table-dark"><th>TOTAL ACTIF</th><td class="text-end fw-bold"><?= number_format($total_actif, 2, ',', ' ') ?> €</td></tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-secondary h-100">
                <div class="card-header bg-secondary text-white fw-bold">PASSIF (Ressources)</div>
                <div class="card-body">
                    <table class="table table-sm table-striped">
                        <thead class="table-light"><tr><th colspan="2">Capitaux Propres</th></tr></thead>
                        <tr><th>Capitaux Propres (Capital + RN)</th><td class="text-end"><?= number_format($bilan_passif['Capitaux Propres (Total)'], 2, ',', ' ') ?> €</td></tr>
                        
                        <thead class="table-light"><tr><th colspan="2">Dettes Long et Moyen Terme</th></tr></thead>
                        <tr><th>Emprunts LT</th><td class="text-end"><?= number_format($bilan_passif['Emprunts LT'], 2, ',', ' ') ?> €</td></tr>
                        
                        <thead class="table-light"><tr><th colspan="2">Dettes Court Terme (Circulantes)</th></tr></thead>
                        <tr><th>Dettes Fournisseurs</th><td class="text-end"><?= number_format($bilan_passif['Dettes Fournisseurs'], 2, ',', ' ') ?> €</td></tr>
                        <tr><th>Dettes Fiscales & Sociales</th><td class="text-end"><?= number_format($bilan_passif['Dettes Fiscales & Sociales'], 2, ',', ' ') ?> €</td></tr>
                        
                        <tr><th></th><td></td></tr>
                        
                        <tr class="table-dark"><th>TOTAL PASSIF</th><td class="text-end fw-bold"><?= number_format($total_passif, 2, ',', ' ') ?> €</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
