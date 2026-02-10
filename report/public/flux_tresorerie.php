<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once 'layout.php';
// Récupération des flux (table dédiée)
$flux = $pdo->query("SELECT * FROM FLUX_TRESORERIE ORDER BY periode")->fetchAll(PDO::FETCH_ASSOC);

// Calcul direct à partir des écritures (compte banque = 2200)
$sql = "
SELECT DATE_FORMAT(date_operation,'%Y-%m-%d') AS dateop,
  SUM(CASE WHEN compte_debite_id = 2200 THEN montant ELSE 0 END) AS bank_debit,
  SUM(CASE WHEN compte_credite_id = 2200 THEN montant ELSE 0 END) AS bank_credit
FROM ECRITURES_COMPTABLES
GROUP BY DATE_FORMAT(date_operation,'%Y-%m')
ORDER BY DATE_FORMAT(date_operation,'%Y-%m');
";
$bank_mov = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Flux de trésorerie</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f8f9fb;padding:20px}</style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Flux de trésorerie</h3>
        <a href="dashboard_graphic.php" class="btn btn-primary btn-sm">Voir Dashboard graphique</a>
    </div>

    <div class="card p-3 mb-3">
        <h5>Table FLUX_TRESORERIE (saisie ou automatique)</h5>
        <?php if(empty($flux)): ?>
            <div class="alert alert-info">Aucune ligne dans FLUX_TRESORERIE.</div>
        <?php else: ?>
            <table class="table table-striped">
                <thead><tr><th>Période</th><th>Flux activité exploitation</th><th>Investissement</th><th>Financement</th><th>Variation trésorerie</th></tr></thead>
                <tbody>
                <?php foreach($flux as $f): ?>
                <tr>
                    <td><?php echo htmlspecialchars($f['periode']); ?></td>
                    <td class="text-end"><?php echo number_format($f['flux_activite_exploit'] ?? 0,2,',',' '); ?></td>
                    <td class="text-end"><?php echo number_format($f['flux_activite_invest'] ?? 0,2,',',' '); ?></td>
                    <td class="text-end"><?php echo number_format($f['flux_activite_finance'] ?? 0,2,',',' '); ?></td>
                    <td class="text-end"><?php echo number_format($f['variation_tresorerie'] ?? 0,2,',',' '); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card p-3">
        <h5>Estimation flux à partir des mouvements bancaires (compte 2200)</h5>
        <p class="small-muted">Agrégation mensuelle depuis ECRITURES_COMPTABLES (compte 2200)</p>
        <table class="table">
            <thead><tr><th>Mois</th><th class="text-end">Total débit (entrée)</th><th class="text-end">Total crédit (sortie)</th><th class="text-end">Net</th></tr></thead>
            <tbody>
            <?php foreach($bank_mov as $bm): 
                $de = (float)$bm['bank_debit'];
                $cr = (float)$bm['bank_credit'];
                $net = $de - $cr;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($bm['dateop']); ?></td>
                    <td class="text-end"><?php echo number_format($de,2,',',' '); ?></td>
                    <td class="text-end"><?php echo number_format($cr,2,',',' '); ?></td>
                    <td class="text-end"><?php echo number_format($net,2,',',' '); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

