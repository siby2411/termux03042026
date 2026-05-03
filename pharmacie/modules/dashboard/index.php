<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
Auth::check();
$page_title = "Dashboard";
include dirname(__DIR__, 2) . '/templates/partials/layout_head.php';

$stats_ca = Database::query("SELECT SUM(montant_total) as total FROM ventes WHERE DATE(date_vente) = CURDATE()")[0];
$stats_rupture = Database::query("SELECT COUNT(*) as nb FROM medicaments WHERE stock_actuel <= stock_min")[0];
?>
<div class="row g-4">
    <div class="col-md-6">
        <div class="omega-card">
            <div class="omega-card-head gold-head">VENTES DU JOUR</div>
            <div class="p-5 text-center">
                <h1 class="fw-bold" style="color:var(--og)"><?= number_format($stats_ca['total'] ?? 0, 0, ',', ' ') ?> F CFA</h1>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="omega-card">
            <div class="omega-card-head" style="background:#dc3545">ALERTES RUPTURE</div>
            <div class="p-5 text-center">
                <h1 class="fw-bold text-danger"><?= $stats_rupture['nb'] ?> Produits</h1>
            </div>
        </div>
    </div>
</div>
<?php include dirname(__DIR__, 2) . '/templates/partials/layout_footer.php'; ?>
