<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Prévisions des ventes - Méthodes quantitatives";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Récupération des données historiques (12 derniers mois)
$historique = $pdo->query("SELECT date_vente, quantite, chiffre_affaires FROM VENTES_HISTORIQUES ORDER BY date_vente ASC LIMIT 12")->fetchAll();
$n = count($historique);
if ($n < 3) {
    $message = "⚠️ Insuffisance de données historiques (minimum 3 mois)";
}

// Calcul de la droite de régression (moindres carrés)
if ($n >= 3) {
    $sum_x = 0; $sum_y = 0; $sum_xy = 0; $sum_x2 = 0;
    for ($i = 0; $i < $n; $i++) {
        $x = $i + 1; // 1,2,...,n
        $y = $historique[$i]['quantite'];
        $sum_x += $x;
        $sum_y += $y;
        $sum_xy += $x * $y;
        $sum_x2 += $x * $x;
    }
    $a = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
    $b = ($sum_y - $a * $sum_x) / $n;
    $previsions_mc = [];
    for ($i = 1; $i <= 6; $i++) {
        $x = $n + $i;
        $previsions_mc[$i] = round($a * $x + $b);
    }
    $resultats['moindres_carres'] = $previsions_mc;
}

// Moyennes mobiles (sur 3 mois)
$moyennes_mobiles = [];
for ($i = 3; $i <= $n; $i++) {
    $moyenne = ($historique[$i-3]['quantite'] + $historique[$i-2]['quantite'] + $historique[$i-1]['quantite']) / 3;
    $moyennes_mobiles[$i] = round($moyenne);
}
$derniere_mm = end($moyennes_mobiles);
$previsions_mm = [];
for ($i = 1; $i <= 6; $i++) {
    $previsions_mm[$i] = $derniere_mm;
}
$resultats['moyennes_mobiles'] = $previsions_mm;

// Ajustement exponentiel (lissage exponentiel simple, alpha=0.3)
$alpha = 0.3;
$lissage = [];
$lissage[0] = $historique[0]['quantite'];
for ($i = 1; $i < $n; $i++) {
    $lissage[$i] = $alpha * $historique[$i]['quantite'] + (1 - $alpha) * $lissage[$i-1];
}
$dernier_lisse = end($lissage);
$previsions_exp = [];
for ($i = 1; $i <= 6; $i++) {
    $previsions_exp[$i] = round($dernier_lisse);
}
$resultats['exponentiel'] = $previsions_exp;

// Coefficients saisonniers
$coeff_saison = [];
$stmt = $pdo->prepare("SELECT mois, coefficient FROM COEFFICIENTS_SAISONNIERS WHERE exercice = ?");
$stmt->execute([date('Y')]);
while ($row = $stmt->fetch()) {
    $coeff_saison[$row['mois']] = $row['coefficient'];
}
$resultats['coeff_saison'] = $coeff_saison;

// Prévisions avec prise en compte de la saisonnalité (basée sur la tendance MC)
$previsions_saison = [];
for ($i = 1; $i <= 12; $i++) {
    $mois = date('n', strtotime("+$i months"));
    $tendance = $previsions_mc[$i] ?? $derniere_mm;
    $coeff = $coeff_saison[$mois] ?? 1;
    $previsions_saison[$i] = round($tendance * $coeff);
}
$resultats['saisonnier'] = $previsions_saison;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Prévisions des ventes - Méthodes quantitatives</h5>
                <small>Moindres carrés, moyennes mobiles, lissage exponentiel, prise en compte de la saisonnalité</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-warning"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary">Méthode des moindres carrés (tendance linéaire)</div>
                            <div class="card-body">
                                <p>Équation : y = <?= round($a,2) ?>x + <?= round($b,2) ?></p>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Mois suivant</th><th>Prévision (unités)</th></tr></thead>
                                        <tbody>
                                            <?php for($i=1; $i<=6; $i++): ?>
                                            <tr><td>M+<?= $i ?></td><td class="text-end"><?= number_format($previsions_mc[$i],0,',',' ') ?></td></tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary">Moyennes mobiles (3 mois)</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Mois suivant</th><th>Prévision (unités)</th></tr></thead>
                                        <tbody>
                                            <?php for($i=1; $i<=6; $i++): ?>
                                            <tr><td>M+<?= $i ?></td><td class="text-end"><?= number_format($previsions_mm[$i],0,',',' ') ?></td></tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary">Lissage exponentiel (α=0.3)</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Mois suivant</th><th>Prévision (unités)</th></tr></thead>
                                        <tbody>
                                            <?php for($i=1; $i<=6; $i++): ?>
                                            <tr><td>M+<?= $i ?></td><td class="text-end"><?= number_format($previsions_exp[$i],0,',',' ') ?></td></tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary">Prévisions intégrant la saisonnalité (tendance MC + coeff)</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Mois</th><th>Prévision (unités)</th><th>Coeff. saisonnier</th></tr></thead>
                                        <tbody>
                                            <?php
                                            $mois_noms = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                                            for($i=1; $i<=12; $i++):
                                                $mois = date('n', strtotime("2026-$i-01"));
                                                $coeff = $coeff_saison[$mois] ?? 1;
                                            ?>
                                            <tr>
                                                <td><?= $mois_noms[$i-1] ?></td>
                                                <td class="text-end"><?= number_format($previsions_saison[$i],0,',',' ') ?></td>
                                                <td class="text-center"><?= number_format($coeff,2) ?></td>
                                            </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-2">
                                    💡 La prévision annuelle = <?= number_format(array_sum($previsions_saison),0,',',' ') ?> unités
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
