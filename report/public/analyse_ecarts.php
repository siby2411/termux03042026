<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Analyse des écarts budgétaires";
$page_icon = "graph-down";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$exercice = $_GET['exercice'] ?? date('Y');
$type_budget = $_GET['type'] ?? 'VENTES';

// Récupération des réalisations à partir des écritures comptables
$realisations = [];
for ($mois = 1; $mois <= 12; $mois++) {
    if ($type_budget == 'VENTES') {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=? AND MONTH(date_ecriture)=? AND compte_credite_id BETWEEN 700 AND 799");
        $stmt->execute([$exercice, $mois]);
        $realisations[$mois] = $stmt->fetchColumn();
    } elseif ($type_budget == 'ACHATS') {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=? AND MONTH(date_ecriture)=? AND compte_debite_id BETWEEN 600 AND 699");
        $stmt->execute([$exercice, $mois]);
        $realisations[$mois] = $stmt->fetchColumn();
    } elseif ($type_budget == 'CHARGES_PERSO') {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=? AND MONTH(date_ecriture)=? AND compte_debite_id = 641");
        $stmt->execute([$exercice, $mois]);
        $realisations[$mois] = $stmt->fetchColumn();
    } else {
        $realisations[$mois] = 0;
    }
}

// Récupération des prévisions
$previsions = [];
$stmt = $pdo->prepare("SELECT mois, montant_prevu FROM BUDGETS WHERE exercice = ? AND type_budget = ?");
$stmt->execute([$exercice, $type_budget]);
while ($row = $stmt->fetch()) {
    $previsions[$row['mois']] = $row['montant_prevu'];
}

$total_prevu = 0;
$total_reel = 0;
$ecarts = [];
for ($i = 1; $i <= 12; $i++) {
    $prevu = $previsions[$i] ?? 0;
    $reel = $realisations[$i] ?? 0;
    $ecart = $reel - $prevu;
    $taux = $prevu > 0 ? ($reel / $prevu) * 100 : 0;
    $ecarts[$i] = ['prevu' => $prevu, 'reel' => $reel, 'ecart' => $ecart, 'taux' => $taux];
    $total_prevu += $prevu;
    $total_reel += $reel;
}
$total_ecart = $total_reel - $total_prevu;
$total_taux = $total_prevu > 0 ? ($total_reel / $total_prevu) * 100 : 0;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Analyse des écarts - <?= $type_budget ?> - <?= $exercice ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center bg-info text-white">
                            <div class="card-body">
                                <h4><?= number_format($total_prevu,0,',',' ') ?> F</h4>
                                <small>Budget prévisionnel</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body">
                                <h4><?= number_format($total_reel,0,',',' ') ?> F</h4>
                                <small>Réalisé</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center bg-warning text-dark">
                            <div class="card-body">
                                <h4><?= number_format($total_ecart,0,',',' ') ?> F (<?= number_format($total_taux,1) ?>%)</h4>
                                <small>Écart global</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Mois</th><th>Prévision (F)</th><th>Réalisé (F)</th><th>Écart (F)</th><th>Taux (%)</th><th>Analyse</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $mois_noms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                            for ($i = 1; $i <= 12; $i++):
                                $couleur = $ecarts[$i]['ecart'] >= 0 ? ($type_budget == 'VENTES' ? 'text-success' : 'text-danger') : ($type_budget == 'VENTES' ? 'text-danger' : 'text-success');
                            ?>
                            <tr>
                                <th><?= $mois_noms[$i-1] ?></th>
                                <td class="text-end"><?= number_format($ecarts[$i]['prevu'],0,',',' ') ?> F</td>
                                <td class="text-end"><?= number_format($ecarts[$i]['reel'],0,',',' ') ?> F</td>
                                <td class="text-end <?= $couleur ?>"><?= number_format($ecarts[$i]['ecart'],0,',',' ') ?> F</td>
                                <td class="text-end"><?= number_format($ecarts[$i]['taux'],1) ?>%</td>
                                <td>
                                    <?php if ($type_budget == 'VENTES' && $ecarts[$i]['ecart'] < 0): ?>
                                        ⚠️ Baisse des ventes
                                    <?php elseif ($type_budget == 'VENTES' && $ecarts[$i]['ecart'] > 0): ?>
                                        ✅ Croissance des ventes
                                    <?php elseif ($type_budget != 'VENTES' && $ecarts[$i]['ecart'] > 0): ?>
                                        ⚠️ Dépassement de budget
                                    <?php elseif ($type_budget != 'VENTES' && $ecarts[$i]['ecart'] < 0): ?>
                                        ✅ Économie réalisée
                                    <?php else: ?>
                                        ➖ Conforme
                                    <?php endif; ?>
                                 </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
