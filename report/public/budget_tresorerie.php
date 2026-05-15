<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Budget et Plan de trésorerie";
$page_icon = "cash-stack";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice = $_GET['exercice'] ?? date('Y');
$mois_actuel = (int)($_GET['mois'] ?? date('m'));

// Récupération des données réelles
$encaissements_reels = [];
$decaissements_reels = [];
for($i = 1; $i <= $mois_actuel; $i++) {
    $encaissements_reels[$i] = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 700 AND 799");
    $encaissements_reels[$i]->execute([$i, $exercice]);
    $encaissements_reels[$i] = $encaissements_reels[$i]->fetchColumn();
    
    $decaissements_reels[$i] = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 600 AND 699");
    $decaissements_reels[$i]->execute([$i, $exercice]);
    $decaissements_reels[$i] = $decaissements_reels[$i]->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for($i = 1; $i <= 12; $i++) {
        $encaissements = (float)$_POST["encaissements_$i"];
        $decaissements = (float)$_POST["decaissements_$i"];
        
        $stmt = $pdo->prepare("INSERT INTO BUDGETS_TRESORERIE (exercice, mois, encaissements_previs, decaissements_previs) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE encaissements_previs = ?, decaissements_previs = ?");
        $stmt->execute([$exercice, $i, $encaissements, $decaissements, $encaissements, $decaissements]);
    }
    $message = "✅ Budget de trésorerie enregistré";
}

// Récupération des prévisions
$previsions = [];
for($i = 1; $i <= 12; $i++) {
    $stmt = $pdo->prepare("SELECT encaissements_previs, decaissements_previs, encaissements_reels, decaissements_reels FROM BUDGETS_TRESORERIE WHERE exercice = ? AND mois = ?");
    $stmt->execute([$exercice, $i]);
    $previsions[$i] = $stmt->fetch();
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-cash-stack"></i> Budget et Plan de trésorerie - <?= $exercice ?></h5>
                <small>Prévision vs Réalisation des flux de trésorerie</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST" class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Mois</th>
                                <th colspan="2">ENCAISSEMENTS</th>
                                <th colspan="2">DECAISSEMENTS</th>
                                <th colspan="2">SOLDE</th>
                            </tr>
                            <tr class="table-light">
                                <th></th><th>Prévision (F)</th><th>Réel (F)</th>
                                <th>Prévision (F)</th><th>Réel (F)</th>
                                <th class="text-success">Solde prévu (F)</th><th class="text-danger">Solde réel (F)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $solde_prevu_cumul = 0;
                            $solde_reel_cumul = 0;
                            for($i = 1; $i <= 12; $i++): 
                                $enc_pre = $previsions[$i]['encaissements_previs'] ?? 0;
                                $dec_pre = $previsions[$i]['decaissements_previs'] ?? 0;
                                $enc_rel = $encaissements_reels[$i] ?? 0;
                                $dec_rel = $decaissements_reels[$i] ?? 0;
                                
                                $solde_mois_prevu = $enc_pre - $dec_pre;
                                $solde_mois_reel = $enc_rel - $dec_rel;
                                
                                $solde_prevu_cumul += $solde_mois_prevu;
                                $solde_reel_cumul += $solde_mois_reel;
                                
                                $mois_nom = date('F', mktime(0,0,0,$i,1));
                            ?>
                            <tr>
                                <th class="text-nowrap"><?= $mois_nom ?></th>
                                <td><input type="number" name="encaissements_<?= $i ?>" class="form-control form-control-sm" value="<?= $enc_pre ?>" step="100000"></td>
                                <td class="text-end fw-bold text-success"><?= number_format($enc_rel, 0, ',', ' ') ?> F</td>
                                <td><input type="number" name="decaissements_<?= $i ?>" class="form-control form-control-sm" value="<?= $dec_pre ?>" step="100000"></td>
                                <td class="text-end fw-bold text-danger"><?= number_format($dec_rel, 0, ',', ' ') ?> F</td>
                                <td class="text-end text-success"><?= number_format($solde_mois_prevu, 0, ',', ' ') ?> F</td>
                                <td class="text-end text-danger"><?= number_format($solde_mois_reel, 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endfor; ?>
                            <tr class="table-secondary fw-bold">
                                <td>CUMUL ANNUELLe</th>
                                <td colspan="2" class="text-center">-</td>
                                <td colspan="2" class="text-center">-</td>
                                <td class="text-end text-success"><?= number_format($solde_prevu_cumul, 0, ',', ' ') ?> F</td>
                                <td class="text-end text-danger"><?= number_format($solde_reel_cumul, 0, ',', ' ') ?> F</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn-omega">Enregistrer le budget de trésorerie</button>
                    </div>
                </form>

                <div class="alert alert-info mt-3">
                    <strong>📊 Analyse des écarts :</strong><br>
                    Écart total = <?= number_format($solde_reel_cumul - $solde_prevu_cumul, 0, ',', ' ') ?> F
                    <?php if($solde_reel_cumul > $solde_prevu_cumul): ?>
                        ✅ Trésorerie réelle supérieure aux prévisions
                    <?php elseif($solde_reel_cumul < $solde_prevu_cumul): ?>
                        ⚠️ Trésorerie réelle inférieure aux prévisions
                    <?php else: ?>
                        ➖ Conforme aux prévisions
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
