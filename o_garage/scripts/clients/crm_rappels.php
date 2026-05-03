<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

// Correction de la requête : on récupère le nom depuis la table clients
$query = "SELECT v.*, c.nom as nom_client, c.telephone as tel_client 
          FROM vehicules v 
          LEFT JOIN clients c ON v.id_client = c.id_client
          WHERE v.prochain_rappel_km > 0";
$alertes = $db->query($query)->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4 text-success"><i class="fab fa-whatsapp me-2"></i>Rappels Vidange Proactifs</h2>
    <div class="row">
        <?php foreach($alertes as $v): 
            $km_restant = $v['prochain_rappel_km'] - $v['dernier_km'];
            $msg = "Bonjour " . ($v['nom_client'] ?? 'Cher Client') . ", votre véhicule " . $v['immatriculation'] . " approche de son échéance de vidange (" . $v['prochain_rappel_km'] . " km).";
            $wa_link = "https://wa.me/" . ($v['tel_client'] ?? $v['telephone_client']) . "?text=" . urlencode($msg);
        ?>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5><?= $v['immatriculation'] ?></h5>
                    <p class="text-muted small"><?= $v['marque'] ?> - <?= $v['nom_client'] ?></p>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Reste : <strong><?= $km_restant ?> KM</strong></span>
                    </div>
                    <a href="<?= $wa_link ?>" target="_blank" class="btn btn-success w-100"><i class="fab fa-whatsapp"></i> Relancer</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
