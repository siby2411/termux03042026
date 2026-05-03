<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

$stats = $db->query("SELECT COUNT(*) as nb, SUM(main_doeuvre) as total FROM interventions WHERE statut='Terminé'")->fetch();
?>
<div class="container">
    <h2 class="fw-bold mb-4">Rapport de Performance : ATELIER</h2>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white p-4 shadow">
                <h6>CA Main d'œuvre</h6>
                <h3><?= number_format($stats['total'] ?? 0, 0, ',', ' ') ?> FCFA</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-dark text-white p-4 shadow">
                <h6>Véhicules réparés</h6>
                <h3><?= $stats['nb'] ?? 0 ?></h3>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
