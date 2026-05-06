<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Prévisions Budgétaires";
$page_icon = "calendar-check";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice_selected = $_GET['exercice'] ?? date('Y');

// Récupération des réalisations
$realisations = [];
for($mois = 1; $mois <= 12; $mois++) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 700 AND 799");
    $stmt->execute([$mois, $exercice_selected]);
    $realisations['ventes'][$mois] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 600 AND 699");
    $stmt->execute([$mois, $exercice_selected]);
    $realisations['achats'][$mois] = $stmt->fetchColumn();
}

// Sauvegarde des prévisions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        for($mois = 1; $mois <= 12; $mois++) {
            $prevision_ventes = (float)($_POST["ventes_$mois"] ?? 0);
            $prevision_achats = (float)($_POST["achats_$mois"] ?? 0);
            
            $stmt = $pdo->prepare("INSERT INTO BUDGETS (exercice, mois, type_budget, montant_prevu) VALUES (?, ?, 'VENTES', ?) ON DUPLICATE KEY UPDATE montant_prevu = ?");
            $stmt->execute([$exercice_selected, $mois, $prevision_ventes, $prevision_ventes]);
            
            $stmt = $pdo->prepare("INSERT INTO BUDGETS (exercice, mois, type_budget, montant_prevu) VALUES (?, ?, 'ACHATS', ?) ON DUPLICATE KEY UPDATE montant_prevu = ?");
            $stmt->execute([$exercice_selected, $mois, $prevision_achats, $prevision_achats]);
        }
        $message = "✅ Prévisions budgétaires enregistrées pour l'exercice $exercice_selected";
    } catch (Exception $e) {
        $message = "❌ Erreur : " . $e->getMessage();
    }
}

// Récupération des prévisions existantes
$previsions = [];
$previsions_ventes = $pdo->prepare("SELECT mois, montant_prevu FROM BUDGETS WHERE exercice = ? AND type_budget = 'VENTES'");
$previsions_ventes->execute([$exercice_selected]);
while($row = $previsions_ventes->fetch()) {
    $previsions['ventes'][$row['mois']] = $row['montant_prevu'];
}

$previsions_achats = $pdo->prepare("SELECT mois, montant_prevu FROM BUDGETS WHERE exercice = ? AND type_budget = 'ACHATS'");
$previsions_achats->execute([$exercice_selected]);
while($row = $previsions_achats->fetch()) {
    $previsions['achats'][$row['mois']] = $row['montant_prevu'];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calendar-check"></i> Budget Prévisionnel vs Réalisations</h5>
                <small>Exercice <?= $exercice_selected ?></small>
            </div>
            <div class="card-body">
                <form method="GET" class="row mb-4">
                    <div class="col-md-3">
                        <select name="exercice" class="form-select" onchange="this.form.submit()">
                            <option value="2024" <?= $exercice_selected == 2024 ? 'selected' : '' ?>>2024</option>
                            <option value="2025" <?= $exercice_selected == 2025 ? 'selected' : '' ?>>2025</option>
                            <option value="<?= date('Y') ?>" <?= $exercice_selected == date('Y') ? 'selected' : '' ?>><?= date('Y') ?></option>
                        </select>
                    </div>
                </form>
                
                <?php if($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr class="text-center">
                                    <th>Mois</th>
                                    <th colspan="2">Prévision Ventes</th>
                                    <th colspan="2">Réalisé Ventes</th>
                                    <th colspan="2">Prévision Achats</th>
                                    <th colspan="2">Réalisé Achats</th>
                                </tr>
                                <tr class="text-center">
                                    <th></th>
                                    <th>Montant</th>
                                    <th>Écart</th>
                                    <th>Montant</th>
                                    <th>% réal.</th>
                                    <th>Montant</th>
                                    <th>Écart</th>
                                    <th>Montant</th>
                                    <th>% réal.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_prev_ventes = 0;
                                $total_real_ventes = 0;
                                $total_prev_achats = 0;
                                $total_real_achats = 0;
                                
                                for($mois = 1; $mois <= 12; $mois++): 
                                    $prev_ventes = $previsions['ventes'][$mois] ?? 0;
                                    $real_ventes = $realisations['ventes'][$mois] ?? 0;
                                    $prev_achats = $previsions['achats'][$mois] ?? 0;
                                    $real_achats = $realisations['achats'][$mois] ?? 0;
                                    
                                    $total_prev_ventes += $prev_ventes;
                                    $total_real_ventes += $real_ventes;
                                    $total_prev_achats += $prev_achats;
                                    $total_real_achats += $real_achats;
                                    
                                    $ecart_ventes = $real_ventes - $prev_ventes;
                                    $ecart_achats = $real_achats - $prev_achats;
                                ?>
                                <tr class="text-center">
                                    <td class="fw-bold"><?= date('F', mktime(0,0,0,$mois,1)) ?></td>
                                    <td><input type="number" name="ventes_<?= $mois ?>" class="form-control form-control-sm" value="<?= number_format($prev_ventes, 0, ',', '') ?>" style="text-align:right"></td>
                                    <td class="<?= $ecart_ventes >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($ecart_ventes, 0, ',', ' ') ?> F</td>
                                    <td class="text-end"><?= number_format($real_ventes, 0, ',', ' ') ?> F</td>
                                    <td class="<?= $prev_ventes > 0 ? ($real_ventes/$prev_ventes*100 >= 100 ? 'text-success' : 'text-warning') : '' ?>">
                                        <?= $prev_ventes > 0 ? number_format(($real_ventes/$prev_ventes)*100, 1) : 0 ?>%
                                    </td>
                                    <td><input type="number" name="achats_<?= $mois ?>" class="form-control form-control-sm" value="<?= number_format($prev_achats, 0, ',', '') ?>" style="text-align:right"></td>
                                    <td class="<?= $ecart_achats <= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($ecart_achats, 0, ',', ' ') ?> F</td>
                                    <td class="text-end"><?= number_format($real_achats, 0, ',', ' ') ?> F</td>
                                    <td class="<?= $prev_achats > 0 ? ($real_achats/$prev_achats*100 <= 100 ? 'text-success' : 'text-warning') : '' ?>">
                                        <?= $prev_achats > 0 ? number_format(($real_achats/$prev_achats)*100, 1) : 0 ?>%
                                    </td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr class="text-center">
                                    <td>TOTAL</td>
                                    <td><?= number_format($total_prev_ventes, 0, ',', ' ') ?> F</td>
                                    <td class="<?= ($total_real_ventes - $total_prev_ventes) >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($total_real_ventes - $total_prev_ventes, 0, ',', ' ') ?> F
                                    </td>
                                    <td><?= number_format($total_real_ventes, 0, ',', ' ') ?> F</td>
                                    <td><?= $total_prev_ventes > 0 ? number_format(($total_real_ventes/$total_prev_ventes)*100, 1) : 0 ?>%</td>
                                    <td><?= number_format($total_prev_achats, 0, ',', ' ') ?> F</td>
                                    <td class="<?= ($total_real_achats - $total_prev_achats) <= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($total_real_achats - $total_prev_achats, 0, ',', ' ') ?> F
                                    </td>
                                    <td><?= number_format($total_real_achats, 0, ',', ' ') ?> F</td>
                                    <td><?= $total_prev_achats > 0 ? number_format(($total_real_achats/$total_prev_achats)*100, 1) : 0 ?>%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="submit" class="btn-omega">Enregistrer les prévisions</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
