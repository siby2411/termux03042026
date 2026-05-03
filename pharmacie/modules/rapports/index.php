<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
Auth::check();

$page_title = "Rapports & Statistiques";
$active_menu = "rapports";
include dirname(__DIR__, 2) . '/templates/partials/layout_head.php';

// Statistiques mensuelles (Exemple simple)
$stats = Database::query("SELECT MONTH(date_vente) as mois, SUM(montant_total) as total FROM ventes WHERE YEAR(date_vente) = 2026 GROUP BY MONTH(date_vente)");
?>
<h3 class="fw-bold mb-4">Analyse de Performance</h3>

<div class="row g-4">
    <div class="col-md-8">
        <div class="omega-card shadow-sm">
            <div class="omega-card-head">CHIFFRE D'AFFAIRES MENSUEL (2026)</div>
            <div class="bg-white p-3">
                <table class="table">
                    <thead><tr><th>Mois</th><th class="text-end">Ventes Total</th></tr></thead>
                    <tbody>
                        <?php foreach($stats as $s): ?>
                        <tr>
                            <td>Mois <?= $s['mois'] ?></td>
                            <td class="text-end fw-bold"><?= number_format($s['total'], 0, ',', ' ') ?> F CFA</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="omega-card shadow-sm">
            <div class="omega-card-head blue-head">ACTIONS</div>
            <div class="bg-white p-4">
                <button class="btn btn-outline-primary w-100 mb-2"><i class="bi bi-file-earmark-pdf"></i> Export PDF</button>
                <button class="btn btn-outline-success w-100"><i class="bi bi-file-earmark-excel"></i> Export Excel</button>
            </div>
        </div>
    </div>
</div>
<?php include dirname(__DIR__, 2) . '/templates/partials/layout_footer.php'; ?>
