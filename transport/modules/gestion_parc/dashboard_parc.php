<?php
require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();
$total_bus = $db->query("SELECT COUNT(*) FROM bus")->fetchColumn();
$reparations = $db->query("SELECT COUNT(*) FROM factures_parc WHERE type_facture='reparation' AND MONTH(date_facture)=MONTH(CURDATE())")->fetchColumn();
$alerte_stock = $db->query("SELECT COUNT(*) FROM pieces_detachees WHERE quantite_stock <= quantite_minimum")->fetchColumn();
$assurances = $db->query("SELECT COUNT(*) FROM assurances_bus WHERE date_fin <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND statut='active'")->fetchColumn();
include __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
    <h2><i class="fas fa-tools"></i> Gestion du Parc Automobile</h2>
    <div class="row mt-4">
        <div class="col-md-3"><div class="card text-center p-3 bg-primary text-white"><i class="fas fa-bus fa-3x"></i><h3 class="mt-2"><?= $total_bus ?></h3><p>Bus en service</p></div></div>
        <div class="col-md-3"><div class="card text-center p-3 bg-success text-white"><i class="fas fa-wrench fa-3x"></i><h3 class="mt-2"><?= $reparations ?></h3><p>Réparations ce mois</p></div></div>
        <div class="col-md-3"><div class="card text-center p-3 bg-warning text-white"><i class="fas fa-boxes fa-3x"></i><h3 class="mt-2"><?= $alerte_stock ?></h3><p>Alertes stock</p></div></div>
        <div class="col-md-3"><div class="card text-center p-3 bg-danger text-white"><i class="fas fa-shield-alt fa-3x"></i><h3 class="mt-2"><?= $assurances ?></h3><p>Assurances à renouveler</p></div></div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
