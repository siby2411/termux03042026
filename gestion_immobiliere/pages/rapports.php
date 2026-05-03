<?php
// On récupère le mois et l'année (par défaut le mois en cours)
$mois_selectionne = $_GET['mois'] ?? date('m');
$annee_selectionnee = $_GET['annee'] ?? date('Y');

// 1. Calcul du CA et des Commissions pour le mois choisi
$stmt = $pdo->prepare("
    SELECT 
        SUM(montant_total) as volume_affaires, 
        SUM(commission_omega) as total_com,
        COUNT(*) as nb_transactions
    FROM finances 
    WHERE MONTH(date_transaction) = ? AND YEAR(date_transaction) = ?
");
$stmt->execute([$mois_selectionne, $annee_selectionnee]);
$bilan = $stmt->fetch();

// 2. Répartition par type (Vente vs Location)
$repartition = $pdo->prepare("
    SELECT type_transaction, SUM(commission_omega) as somme 
    FROM finances 
    WHERE MONTH(date_transaction) = ? AND YEAR(date_transaction) = ?
    GROUP BY type_transaction
");
$repartition->execute([$mois_selectionne, $annee_selectionnee]);
$types = $repartition->fetchAll();
?>

<div class="card p-4 shadow-sm border-0 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up-arrow text-warning"></i> Analyse de Performance</h4>
        <form class="d-flex gap-2">
            <input type="hidden" name="page" value="rapports">
            <select name="mois" class="form-select form-select-sm">
                <?php for($m=1; $m<=12; $m++): ?>
                    <option value="<?= sprintf('%02d', $m) ?>" <?= $mois_selectionne == $m ? 'selected' : '' ?>>
                        <?= strftime('%B', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="annee" class="form-select form-select-sm">
                <option value="2026" selected>2026</option>
                <option value="2025">2025</option>
            </select>
            <button type="submit" class="btn btn-dark btn-sm">Filtrer</button>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light text-center">
                <h6 class="text-muted small">VOLUME D'AFFAIRES BRUT</h6>
                <h3 class="fw-bold"><?= number_format($bilan['volume_affaires'] ?? 0, 0, ',', ' ') ?> <small class="fs-6">F</small></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-warning-subtle text-center">
                <h6 class="text-muted small">NET COMMISSIONS OMEGA</h6>
                <h3 class="fw-bold text-dark"><?= number_format($bilan['total_com'] ?? 0, 0, ',', ' ') ?> <small class="fs-6">F</small></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light text-center">
                <h6 class="text-muted small">SUCCÈS CLOSÉS</h6>
                <h3 class="fw-bold"><?= $bilan['nb_transactions'] ?? 0 ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-4 shadow-sm border-0 h-100">
            <h5 class="fw-bold mb-3 small text-muted">RÉPARTITION DES REVENUS</h5>
            <div class="progress-stacked" style="height: 30px;">
                <?php foreach($types as $t): 
                    $percent = ($bilan['total_com'] > 0) ? ($t['somme'] / $bilan['total_com']) * 100 : 0;
                    $color = ($t['type_transaction'] == 'Vente') ? 'bg-primary' : 'bg-info';
                ?>
                <div class="progress" role="progressbar" style="width: <?= $percent ?>%">
                    <div class="progress-bar <?= $color ?>"><?= $t['type_transaction'] ?> (<?= round($percent) ?>%)</div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-3 small text-muted">
                * Analyse basée sur les transactions validées pour la période choisie.
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 shadow-sm border-0 h-100">
            <h5 class="fw-bold mb-3 small text-muted text-uppercase">Objectif Mars 2026</h5>
            <?php 
                $objectif = 5000000; // Exemple d'objectif à 5 millions
                $current = $bilan['total_com'] ?? 0;
                $perf = min(100, ($current / $objectif) * 100);
            ?>
            <div class="d-flex justify-content-between mb-1">
                <span>Progression : <?= round($perf) ?>%</span>
                <span><?= number_format($objectif, 0, ',', ' ') ?> F</span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-success" style="width: <?= $perf ?>%"></div>
            </div>
        </div>
    </div>
</div>
