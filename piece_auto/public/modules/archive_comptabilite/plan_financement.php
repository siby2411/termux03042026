<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Plan de financement - Bilans prévisionnels";
$page_icon = "calendar-check";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice = $_GET['exercice'] ?? (date('Y') + 1);

// Prévisions par défaut
$previsions = [
    'CA_prevu' => 35000000,
    'CA_prevu_n1' => 38000000,
    'investissements' => 5000000,
    'augmentation_capital' => 2000000,
    'emprunt' => 3000000,
    'remboursement' => 1000000,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $previsions = [
        'CA_prevu' => (float)$_POST['CA_prevu'],
        'CA_prevu_n1' => (float)$_POST['CA_prevu_n1'],
        'investissements' => (float)$_POST['investissements'],
        'augmentation_capital' => (float)$_POST['augmentation_capital'],
        'emprunt' => (float)$_POST['emprunt'],
        'remboursement' => (float)$_POST['remboursement'],
    ];
    
    // Calcul du BFR prévisionnel
    $bfr_prevu = $previsions['CA_prevu'] * 0.3;
    $bfr_prevu_n1 = $previsions['CA_prevu_n1'] * 0.3;
    $variation_bfr = $bfr_prevu_n1 - $bfr_prevu;
    
    // Calcul des ressources
    $ressources = $previsions['augmentation_capital'] + $previsions['emprunt'];
    
    // Calcul des emplois
    $emplois = $previsions['investissements'] + $variation_bfr + $previsions['remboursement'];
    
    $financement = $ressources - $emplois;
    
    $message = "✅ Plan de financement calculé - Besoin net : " . number_format(abs($financement), 0, ',', ' ') . " FCFA";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calendar-check"></i> Plan de financement - Exercice <?= $exercice ?></h5>
                <small>Prévision des besoins et ressources</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">📈 RESSOURCES</div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <tr><td>Capacité d'autofinancement (CAF)预估</td><td class="text-end">+ 3 500 000 F</td></tr>
                                    <tr><td>Augmentation de capital</td><td class="text-end text-success">+ <?= number_format($previsions['augmentation_capital'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Nouveaux emprunts</td><td class="text-end text-success">+ <?= number_format($previsions['emprunt'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Cessions d'actifs</td><td class="text-end text-success">+ 500 000 F</td></tr>
                                    <tr class="bg-success text-white fw-bold"><td>TOTAL RESSOURCES</td><td class="text-end"><?= number_format($previsions['augmentation_capital'] + $previsions['emprunt'] + 4000000, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-danger text-white">📉 EMPLOIS</div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <tr><td>Investissements</td><td class="text-end text-danger">- <?= number_format($previsions['investissements'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Variation du BFR</td><td class="text-end text-danger">- <?= number_format(($previsions['CA_prevu_n1'] - $previsions['CA_prevu']) * 0.3, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Remboursement d'emprunts</td><td class="text-end text-danger">- <?= number_format($previsions['remboursement'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Dividendes</td><td class="text-end text-danger">- 500 000 F</td></tr>
                                    <tr class="bg-danger text-white fw-bold"><td>TOTAL EMPLOIS</td><td class="text-end"><?= number_format($previsions['investissements'] + ($previsions['CA_prevu_n1'] - $previsions['CA_prevu']) * 0.3 + $previsions['remboursement'] + 500000, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-info text-white">💰 BESOIN / DÉGAGEMENT NET</div>
                    <div class="card-body text-center">
                        <?php 
                        $ressources_total = $previsions['augmentation_capital'] + $previsions['emprunt'] + 4000000;
                        $emplois_total = $previsions['investissements'] + ($previsions['CA_prevu_n1'] - $previsions['CA_prevu']) * 0.3 + $previsions['remboursement'] + 500000;
                        $solde = $ressources_total - $emplois_total;
                        ?>
                        <h3 class="<?= $solde >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format(abs($solde), 0, ',', ' ') ?> FCFA
                        </h3>
                        <p><?= $solde >= 0 ? '✅ Dégagement net de trésorerie' : '⚠️ Besoin net de financement' ?></p>
                    </div>
                </div>

                <!-- Bilan prévisionnel simplifié -->
                <div class="card mt-4">
                    <div class="card-header bg-dark text-white">📊 Bilan prévisionnel simplifié (N+1)</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>ACTIF (estimé)</h6>
                                <table class="table table-sm">
                                    <tr><td>Actif immobilisé</td><td class="text-end">15 000 000 F</td></tr>
                                    <tr><td>Actif circulant</td><td class="text-end"><?= number_format($previsions['CA_prevu_n1'] * 0.3, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Trésorerie</td><td class="text-end"><?= number_format(max(0, $solde), 0, ',', ' ') ?> F</td></tr>
                                    <tr class="fw-bold"><td>TOTAL ACTIF</td><td class="text-end"><?= number_format(15000000 + $previsions['CA_prevu_n1'] * 0.3 + max(0, $solde), 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>PASSIF (estimé)</h6>
                                <table class="table table-sm">
                                    <tr><td>Capitaux propres</td><td class="text-end"><?= number_format(8000000 + $previsions['augmentation_capital'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Dettes financières</td><td class="text-end"><?= number_format(5000000 + $previsions['emprunt'] - $previsions['remboursement'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Dettes fournisseurs</td><td class="text-end"><?= number_format($previsions['CA_prevu_n1'] * 0.15, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="fw-bold"><td>TOTAL PASSIF</td><td class="text-end"><?= number_format(8000000 + $previsions['augmentation_capital'] + 5000000 + $previsions['emprunt'] - $previsions['remboursement'] + $previsions['CA_prevu_n1'] * 0.15, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
