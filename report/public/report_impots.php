<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Report à nouveau & Gestion des Impôts";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupération des reports à nouveau
$reports = $pdo->query("
    SELECT r.*, c.intitule_compte 
    FROM REPORT_NOUVEAU_DETAILLE r
    JOIN PLAN_COMPTABLE_UEMOA c ON r.compte_impacte = c.compte_id
    ORDER BY r.exercice DESC, r.date_operation DESC
")->fetchAll();

// Récupération des impôts
$impots = $pdo->query("
    SELECT * FROM IMPOTS_TAXES 
    ORDER BY exercice DESC, date_echeance DESC
")->fetchAll();

// Calcul du report à nouveau net
$report_net = 0;
foreach($reports as $r) {
    if($r['type_mouvement'] == 'BENEFICE') $report_net += $r['montant'];
    elseif($r['type_mouvement'] == 'PERTE') $report_net -= $r['montant'];
    elseif($r['type_mouvement'] == 'IMPOSITION') $report_net -= $r['montant'];
    elseif($r['type_mouvement'] == 'RESERVE') $report_net -= $r['montant'];
}

// Total impôts à payer
$impots_a_payer = 0;
foreach($impots as $i) {
    if($i['statut'] == 'EN_ATTENTE') $impots_a_payer += $i['montant_theorique'];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-file-text"></i> Report à nouveau & Gestion des Impôts</h5>
                <small>Suivi des résultats reportés et des obligations fiscales</small>
            </div>
            <div class="card-body">
                
                <!-- Synthèse -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <i class="bi bi-arrow-repeat fs-2"></i>
                                <h4><?= number_format($report_net, 0, ',', ' ') ?> F</h4>
                                <small>Report à nouveau net cumulé</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <i class="bi bi-calculator fs-2"></i>
                                <h4><?= number_format($impots_a_payer, 0, ',', ' ') ?> F</h4>
                                <small>Impôts à payer</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <i class="bi bi-piggy-bank fs-2"></i>
                                <h4><?= number_format($report_net - $impots_a_payer, 0, ',', ' ') ?> F</h4>
                                <small>Bénéfice net après impôts</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tableau des reports -->
                <h6><i class="bi bi-arrow-right-circle"></i> Historique des reports à nouveau</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Date</th><th>Exercice</th><th>Type</th><th>Compte</th><th>Libellé</th><th class="text-end">Montant</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($reports as $r): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($r['date_operation'])) ?></td>
                                <td class="text-center"><?= $r['exercice'] ?></td>
                                <td>
                                    <span class="badge <?= $r['type_mouvement'] == 'BENEFICE' ? 'bg-success' : ($r['type_mouvement'] == 'PERTE' ? 'bg-danger' : 'bg-warning') ?>">
                                        <?= $r['type_mouvement'] ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $r['compte_impacte'] ?> (<?= $r['intitule_compte'] ?>)</td>
                                <td><?= htmlspecialchars($r['libelle']) ?></td>
                                <td class="text-end <?= $r['type_mouvement'] == 'BENEFICE' ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($r['montant'], 0, ',', ' ') ?> F
                                 </td>
                            </td>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr class="fw-bold">
                                <td colspan="5" class="text-end">SOLDE REPORT À NOUVEAU :</td>
                                <td class="text-end text-primary"><?= number_format($report_net, 0, ',', ' ') ?> F</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Tableau des impôts -->
                <h6><i class="bi bi-receipt"></i> Échéancier fiscal</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Exercice</th><th>Trim.</th><th>Type</th><th>Base</th><th>Taux</th><th>Montant dû</th><th>Échéance</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($impots as $i): ?>
                            <tr>
                                <td class="text-center"><?= $i['exercice'] ?></td>
                                <td class="text-center">T<?= $i['trimestre'] ?></td>
                                <td><?= $i['type_impot'] ?></td>
                                <td class="text-end"><?= number_format($i['base_calcul'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= $i['taux'] ?>%</td>
                                <td class="text-end"><?= number_format($i['montant_theorique'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($i['date_echeance'])) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $i['statut'] == 'PAYE' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $i['statut'] ?>
                                    </span>
                                </td>
                            </td>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
