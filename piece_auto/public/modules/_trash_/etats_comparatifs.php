<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "États financiers comparatifs";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$annee1 = $_GET['annee1'] ?? (date('Y')-1);
$annee2 = $_GET['annee2'] ?? date('Y');

// Calcul des agrégats pour chaque année
for($y = $annee1; $y <= $annee2; $y++) {
    $ca[$y] = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 700 AND 799");
    $ca[$y]->execute([$y]); $ca[$y] = $ca[$y]->fetchColumn();
    
    $charges[$y] = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 600 AND 699");
    $charges[$y]->execute([$y]); $charges[$y] = $charges[$y]->fetchColumn();
    
    $resultat[$y] = $ca[$y] - $charges[$y];
    $croissance[$y] = ($y > $annee1) ? (($ca[$y] - $ca[$y-1]) / max($ca[$y-1],1) * 100) : 0;
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> États financiers comparatifs - N-1 / N</h5>
                <small>Analyse des performances sur deux exercices</small>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3"><label>Année N-1</label><input type="number" name="annee1" class="form-control" value="<?= $annee1 ?>"></div>
                    <div class="col-md-3"><label>Année N</label><input type="number" name="annee2" class="form-control" value="<?= $annee2 ?>"></div>
                    <div class="col-md-3"><button type="submit" class="btn-omega mt-4">Comparer</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Indicateur</th>
                                <th><?= $annee1 ?></th>
                                <th><?= $annee2 ?></th>
                                <th>Variation (F)</th>
                                <th>Variation (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="fw-bold">Chiffre d'Affaires</td>
                                <td class="text-end"><?= number_format($ca[$annee1], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($ca[$annee2], 0, ',', ' ') ?> F</td>
                                <td class="text-end <?= ($ca[$annee2]-$ca[$annee1])>=0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($ca[$annee2]-$ca[$annee1], 0, ',', ' ') ?> F
                                </td>
                                <td class="text-center <?= $croissance[$annee2]>=0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($croissance[$annee2], 2) ?>%
                                </td>
                            </tr>
                            <tr><td class="fw-bold">Charges totales</td>
                                <td class="text-end"><?= number_format($charges[$annee1], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($charges[$annee2], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($charges[$annee2]-$charges[$annee1], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= number_format(($charges[$annee2]-$charges[$annee1])/max($charges[$annee1],1)*100, 2) ?>%</td>
                            </tr>
                            <tr class="table-primary fw-bold">
                                <td>Résultat net</td>
                                <td class="text-end"><?= number_format($resultat[$annee1], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($resultat[$annee2], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($resultat[$annee2]-$resultat[$annee1], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= number_format(($resultat[$annee2]-$resultat[$annee1])/max(abs($resultat[$annee1]),1)*100, 2) ?>%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
