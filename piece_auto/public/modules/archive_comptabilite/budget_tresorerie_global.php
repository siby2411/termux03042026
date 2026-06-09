<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Budget de trésorerie";
$page_icon = "cash-stack";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice = $_GET['exercice'] ?? date('Y');
$mois_courant = (int)($_GET['mois'] ?? date('m'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 1; $i <= 12; $i++) {
        $enc_pre = (float)($_POST["enc_pre_$i"] ?? 0);
        $dec_pre = (float)($_POST["dec_pre_$i"] ?? 0);
        $stmt = $pdo->prepare("INSERT INTO PREVISIONS_TRESORERIE_GLOBALE (exercice, mois, encaissements_prevu, decaissements_prevu) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE encaissements_prevu=?, decaissements_prevu=?");
        $stmt->execute([$exercice, $i, $enc_pre, $dec_pre, $enc_pre, $dec_pre]);
    }
    $message = "✅ Budget de trésorerie enregistré";
}

$previsions = [];
for ($i = 1; $i <= 12; $i++) {
    $stmt = $pdo->prepare("SELECT encaissements_prevu, decaissements_prevu FROM PREVISIONS_TRESORERIE_GLOBALE WHERE exercice = ? AND mois = ?");
    $stmt->execute([$exercice, $i]);
    $previsions[$i] = $stmt->fetch();
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Budget de trésorerie - Exercice <?= $exercice ?></h5>
                <small>Prévisions des flux de trésorerie</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Mois</th><th>Encaissements prévus (F)</th><th>Décaissements prévus (F)</th><th>Solde mensuel prévu (F)</th><th>Solde cumulé (F)</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                $mois_noms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                                $cumul = 0;
                                for ($i = 1; $i <= 12; $i++):
                                    $enc = $previsions[$i]['encaissements_prevu'] ?? 0;
                                    $dec = $previsions[$i]['decaissements_prevu'] ?? 0;
                                    $solde_mois = $enc - $dec;
                                    $cumul += $solde_mois;
                                ?>
                                    <tr>
                                        <th><?= $mois_noms[$i-1] ?></th>
                                        <td><input type="number" name="enc_pre_<?= $i ?>" class="form-control" step="100000" value="<?= $enc ?>"></td>
                                        <td><input type="number" name="dec_pre_<?= $i ?>" class="form-control" step="100000" value="<?= $dec ?>"></td>
                                        <td class="text-end"><?= number_format($solde_mois,0,',',' ') ?> F</td>
                                        <td class="text-end"><?= number_format($cumul,0,',',' ') ?> F</td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot>
                                <tr><td colspan="5" class="text-center"><button type="submit" class="btn-omega">Enregistrer</button></td></tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
