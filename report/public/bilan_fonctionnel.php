<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Bilan fonctionnel - Analyse financière";
$page_icon = "pie-chart";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des données du bilan comptable
$actif_immobilise = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 20 AND 29");
$actif_immobilise->execute([$exercice]);
$actif_immobilise = $actif_immobilise->fetchColumn();

$stocks = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 30 AND 39");
$stocks->execute([$exercice]);
$stocks = $stocks->fetchColumn();

$creances = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 40 AND 49");
$creances->execute([$exercice]);
$creances = $creances->fetchColumn();

$tresorerie_actif = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 50 AND 59");
$tresorerie_actif->execute([$exercice]);
$tresorerie_actif = $tresorerie_actif->fetchColumn();

$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$capitaux_propres = $capitaux_propres->fetchColumn();

$dettes_financieres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id IN (164, 165, 166)");
$dettes_financieres->execute([$exercice]);
$dettes_financieres = $dettes_financieres->fetchColumn();

$dettes_exploitation = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 40 AND 49");
$dettes_exploitation->execute([$exercice]);
$dettes_exploitation = $dettes_exploitation->fetchColumn();

$tresorerie_passif = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id IN (521, 57)");
$tresorerie_passif->execute([$exercice]);
$tresorerie_passif = $tresorerie_passif->fetchColumn();

// Calculs des agrégats fonctionnels
$actif_circulant = $stocks + $creances;
$actif_total = $actif_immobilise + $actif_circulant + $tresorerie_actif;
$ressources_stables = $capitaux_propres + $dettes_financieres;
$passif_circulant = $dettes_exploitation + $tresorerie_passif;
$passif_total = $ressources_stables + $passif_circulant;

// FRNG (Fonds de Roulement Net Global)
$frng = $ressources_stables - $actif_immobilise;

// BFR (Besoin en Fonds de Roulement)
$bfr = $actif_circulant - $passif_circulant;

// Trésorerie Nette
$tn = $tresorerie_actif - $tresorerie_passif;

// Vérification : FRNG = BFR + TN
$verification = $frng == ($bfr + $tn);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-pie-chart"></i> Bilan fonctionnel - Exercice <?= $exercice ?></h5>
                <small>Retraitements pour analyse financière</small>
            </div>
            <div class="card-body">

                <div class="row">
                    <!-- ACTIF FONCTIONNEL -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-danger text-white">ACTIF FONCTIONNEL</div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <tr class="bg-light"><td colspan="2"><strong>ACTIF IMMOBILISÉ</strong></td></tr>
                                    <tr><td class="ps-3">Immobilisations corporelles & incorporelles</td><td class="text-end"><?= number_format($actif_immobilise, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="bg-light"><td colspan="2"><strong>ACTIF CIRCULANT</strong></td></tr>
                                    <tr><td class="ps-3">Stocks</td><td class="text-end"><?= number_format($stocks, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td class="ps-3">Créances clients et autres</td><td class="text-end"><?= number_format($creances, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td class="ps-3 fw-bold">Total Actif Circulant</td><td class="text-end fw-bold"><?= number_format($actif_circulant, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="bg-light"><td colspan="2"><strong>TRÉSORERIE ACTIF</strong></td></tr>
                                    <tr><td class="ps-3">Disponibilités (banque, caisse)</td><td class="text-end"><?= number_format($tresorerie_actif, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="bg-primary text-white fw-bold"><td>TOTAL ACTIF</td><td class="text-end"><?= number_format($actif_total, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- PASSIF FONCTIONNEL -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">PASSIF FONCTIONNEL</div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <tr class="bg-light"><td colspan="2"><strong>RESSOURCES STABLES</strong></td></tr>
                                    <tr><td class="ps-3">Capitaux propres</td><td class="text-end"><?= number_format($capitaux_propres, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td class="ps-3">Dettes financières (LT)</td><td class="text-end"><?= number_format($dettes_financieres, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="fw-bold"><td class="ps-3">Total Ressources Stables</td><td class="text-end"><?= number_format($ressources_stables, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="bg-light"><td colspan="2"><strong>PASSIF CIRCULANT</strong></td></tr>
                                    <tr><td class="ps-3">Dettes fournisseurs</td><td class="text-end"><?= number_format($dettes_exploitation, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="fw-bold"><td class="ps-3">Total Passif Circulant</td><td class="text-end"><?= number_format($passif_circulant, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="bg-light"><td colspan="2"><strong>TRÉSORERIE PASSIF</strong></td></tr>
                                    <tr><td class="ps-3">Dettes bancaires (CT)</td><td class="text-end"><?= number_format($tresorerie_passif, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="bg-success text-white fw-bold"><td>TOTAL PASSIF</td><td class="text-end"><?= number_format($passif_total, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INDICATEURS FONCTIONNELS -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($frng, 0, ',', ' ') ?> F</h4>
                                <small>FRNG - Fonds de Roulement Net Global</small>
                                <p class="mt-2 small"><?= $frng >= 0 ? '✅ Ressources stables > actif immobilisé' : '⚠️ Insuffisance de ressources stables' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h4><?= number_format($bfr, 0, ',', ' ') ?> F</h4>
                                <small>BFR - Besoin en Fonds de Roulement</small>
                                <p class="mt-2 small"><?= $bfr > 0 ? '⚠️ Besoin de financement du cycle d\'exploitation' : '✅ Ressource dégagée par le cycle' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($tn, 0, ',', ' ') ?> F</h4>
                                <small>Trésorerie Nette</small>
                                <p class="mt-2 small"><?= $tn >= 0 ? '✅ Trésorerie positive' : '⚠️ Découvert bancaire' ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LIEN FRNG = BFR + TN -->
                <div class="alert <?= $verification ? 'alert-success' : 'alert-danger' ?> text-center">
                    <strong>🔗 Vérification fondamentale :</strong> FRNG = BFR + TN<br>
                    <?= number_format($frng, 0, ',', ' ') ?> F = <?= number_format($bfr, 0, ',', ' ') ?> F + <?= number_format($tn, 0, ',', ' ') ?> F
                    <?php if($verification): ?>
                        ✅ ÉQUATION VÉRIFIÉE
                    <?php else: ?>
                        ⚠️ ÉCART À CORRIGER
                    <?php endif; ?>
                </div>

                <!-- INTERPRÉTATION -->
                <div class="alert alert-secondary mt-3">
                    <strong>📊 INTERPRÉTATION DE L'ÉQUILIBRE FINANCIER :</strong><br>
                    <?php if($frng > 0 && $bfr > 0 && $tn > 0): ?>
                        🟢 <strong>Équilibre financier optimal</strong> - L'entreprise dispose de ressources stables pour financer son BFR et dégage une trésorerie positive.
                    <?php elseif($frng > 0 && $bfr > 0 && $tn < 0): ?>
                        🟡 <strong>Situation tendue</strong> - Le BFR est partiellement financé par découvert bancaire. Réduire le BFR ou augmenter le FRNG.
                    <?php elseif($frng < 0): ?>
                        🔴 <strong>Situation critique</strong> - Le FRNG négatif indique un déséquilibre structurel. Nécessité d'une augmentation de capital ou de renégocier les dettes.
                    <?php else: ?>
                        ⚪ <strong>Situation normale</strong> - L'équilibre financier est respecté.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
