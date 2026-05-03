<?php 
require_once '../../includes/header.php'; 
$lavage = $db->query("SELECT SUM(montant) FROM lavage_transactions")->fetchColumn() ?: 0;
$repa = $db->query("SELECT SUM(cout_main_doeuvre) FROM fiches_intervention WHERE statut='Terminé'")->fetchColumn() ?: 0;
$pieces = $db->query("SELECT SUM(total_vente) FROM factures_pieces")->fetchColumn() ?: 0;
?>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card bg-info text-white p-4">
            <h6>CA Lavage</h6>
            <h3><?= number_format($lavage, 0, ',', ' ') ?> F</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white p-4">
            <h6>CA Réparations (MO)</h6>
            <h3><?= number_format($repa, 0, ',', ' ') ?> F</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white p-4">
            <h6>CA Pièces Détachées</h6>
            <h3><?= number_format($pieces, 0, ',', ' ') ?> F</h3>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
