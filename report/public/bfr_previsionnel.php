<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Besoin en Fonds de Roulement (BFR) - Prévisionnel";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];
$exercice = $_GET['exercice'] ?? (date('Y') + 1);

// Données réelles pour référence
$ca_reel = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 700 AND 799");
$ca_reel->execute([date('Y')]);
$ca_reel = $ca_reel->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ca_prevu = (float)$_POST['ca_prevu'];
    $delai_creances = (int)$_POST['delai_creances'];
    $delai_dettes = (int)$_POST['delai_dettes'];
    $rotation_stocks = (int)$_POST['rotation_stocks'];
    $bfre_fixe = (float)$_POST['bfre_fixe'];
    
    // Calcul du BFR selon méthode normative
    $creances = ($ca_prevu * $delai_creances) / 360;
    $dettes = ($ca_prevu * $delai_dettes) / 360;
    $stocks = ($ca_prevu * $rotation_stocks) / 360;
    
    $bfre_normatif = $stocks + $creances - $dettes;
    $bfre_variable = $bfre_normatif - $bfre_fixe;
    
    // Sauvegarde
    $stmt = $pdo->prepare("INSERT INTO PREVISIONS_BFR_DETAIL (exercice, chiffre_affaires, delai_creances, delai_dettes, rotation_stocks, bfre_previsionnel, bfre_fixe, bfre_variable) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$exercice, $ca_prevu, $delai_creances, $delai_dettes, $rotation_stocks, $bfre_normatif, $bfre_fixe, $bfre_variable]);
    
    $resultats = [
        'ca_prevu' => $ca_prevu,
        'creances' => $creances,
        'dettes' => $dettes,
        'stocks' => $stocks,
        'bfre_normatif' => $bfre_normatif,
        'bfre_fixe' => $bfre_fixe,
        'bfre_variable' => $bfre_variable
    ];
    $message = "✅ Calcul BFR prévisionnel effectué";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Besoin en Fonds de Roulement (BFR) - Prévisionnel</h5>
                <small>Méthode normative - Calcul du BFRE</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <strong>📊 Formules de calcul :</strong><br>
                    • Créances = CA prévu × Délai clients / 360<br>
                    • Dettes fournisseurs = CA prévu × Délai fournisseurs / 360<br>
                    • Stocks = CA prévu × Rotation stocks / 360<br>
                    • <strong>BFRE = Stocks + Créances - Dettes</strong>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📈 Données de base</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6">
                                        <label>CA réel N</label>
                                        <input type="text" class="form-control" value="<?= number_format($ca_reel, 0, ',', ' ') ?> F" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label>CA prévu N+1 (F)</label>
                                        <input type="number" name="ca_prevu" class="form-control" required value="<?= $ca_reel * 1.1 ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Délai clients (jours)</label>
                                        <input type="number" name="delai_creances" class="form-control" value="45" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Délai fournisseurs (jours)</label>
                                        <input type="number" name="delai_dettes" class="form-control" value="30" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Rotation stocks (jours)</label>
                                        <input type="number" name="rotation_stocks" class="form-control" value="60" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label>BFRE fixe (F) - optionnel</label>
                                        <input type="number" name="bfre_fixe" class="form-control" value="0">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-omega w-100">Calculer le BFR prévisionnel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <?php if(!empty($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📊 Résultats du calcul</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><td>Stocks (CA × rotation/360)</td><td class="text-end fw-bold"><?= number_format($resultats['stocks'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Créances clients (CA × délai/360)</td><td class="text-end fw-bold"><?= number_format($resultats['creances'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Dettes fournisseurs (CA × délai/360)</td><td class="text-end text-danger">- <?= number_format($resultats['dettes'], 0, ',', ' ') ?> F</td></tr>
                                    <tr class="bg-primary text-white fw-bold"><td>BFRE NORMATIF</td><td class="text-end"><?= number_format($resultats['bfre_normatif'], 0, ',', ' ') ?> F</td></tr>
                                    <?php if($resultats['bfre_fixe'] > 0): ?>
                                    <tr><td>BFRE fixe</td><td class="text-end"><?= number_format($resultats['bfre_fixe'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>BFRE variable</td><td class="text-end"><?= number_format($resultats['bfre_variable'], 0, ',', ' ') ?> F</td></tr>
                                    <?php endif; ?>
                                </table>

                                <div class="alert <?= $resultats['bfre_normatif'] > 0 ? 'alert-warning' : 'alert-success' ?> mt-2">
                                    <?php if($resultats['bfre_normatif'] > 0): ?>
                                        ⚠️ Besoin de financement du cycle d'exploitation : <strong><?= number_format($resultats['bfre_normatif'], 0, ',', ' ') ?> F</strong>
                                    <?php else: ?>
                                        ✅ Ressource dégagée par le cycle d'exploitation : <strong><?= number_format(abs($resultats['bfre_normatif']), 0, ',', ' ') ?> F</strong>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Méthode des bilans prévisionnels -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">📋 Méthode des bilans prévisionnels</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Bilan prévisionnel N+1 (extrait)</h6>
                                <table class="table table-sm">
                                    <tr><th>ACTIF</th><th class="text-end">Montant</th><th>PASSIF</th><th class="text-end">Montant</th></tr>
                                    <tr><td>Actif immobilisé</td><td class="text-end">15 000 000 F</td><td>Capitaux propres</td><td class="text-end">10 000 000 F</td></tr>
                                    <tr><td>Stocks</td><td class="text-end"><?= number_format($resultats['stocks'] ?? 0, 0, ',', ' ') ?> F</td><td>Dettes financières</td><td class="text-end">5 000 000 F</td></tr>
                                    <tr><td>Créances</td><td class="text-end"><?= number_format($resultats['creances'] ?? 0, 0, ',', ' ') ?> F</td><td>Dettes fournisseurs</td><td class="text-end"><?= number_format($resultats['dettes'] ?? 0, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="fw-bold"><td>TOTAL ACTIF</td><td class="text-end"><?= number_format(15000000 + ($resultats['stocks'] ?? 0) + ($resultats['creances'] ?? 0), 0, ',', ' ') ?> F</td>
                                    <td>TOTAL PASSIF</td><td class="text-end"><?= number_format(10000000 + 5000000 + ($resultats['dettes'] ?? 0), 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Calcul du FRNG prévisionnel</h6>
                                <div class="alert alert-secondary">
                                    FRNG = Ressources stables - Actif immobilisé<br>
                                    FRNG = (10M + 5M) - 15M = <strong>0 F</strong>
                                </div>
                                <div class="alert <?= ($resultats['bfre_normatif'] ?? 0) > 0 ? 'alert-danger' : 'alert-success' ?>">
                                    <strong>Financement du BFRE :</strong><br>
                                    <?php if(($resultats['bfre_normatif'] ?? 0) > 0): ?>
                                        ⚠️ Le BFRE est positif (besoin), mais le FRNG est nul → risque de trésorerie négative
                                    <?php else: ?>
                                        ✅ Le BFRE est négatif (ressource), la trésorerie sera positive
                                    <?php endif; ?>
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
