<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Coût du capital et WACC";
$page_icon = "percent";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des données
$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$cp = $capitaux_propres->fetchColumn();

$dettes = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id IN (164, 165, 166)");
$dettes->execute([$exercice]);
$dettes = $dettes->fetchColumn();

$charges_financieres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id = 671");
$charges_financieres->execute([$exercice]);
$charges_financieres = $charges_financieres->fetchColumn();

$total_financement = $cp + $dettes;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taux_sans_risque = (float)$_POST['taux_sans_risque'];
    $prime_risque = (float)$_POST['prime_risque'];
    $beta = (float)$_POST['beta'];
    
    // Coût des capitaux propres (MEDAF)
    $cout_cp = $taux_sans_risque + ($beta * $prime_risque);
    
    // Coût de la dette après IS
    $taux_dette = $dettes > 0 ? ($charges_financieres / $dettes) * 100 : 0;
    $cout_dette = $taux_dette * (1 - 0.25);
    
    // WACC
    $wacc = ($cp / $total_financement) * $cout_cp + ($dettes / $total_financement) * $cout_dette;
    
    // Sauvegarde
    $stmt = $pdo->prepare("INSERT INTO COUT_CAPITAL (exercice, taux_sans_risque, prime_risque, beta, cout_capitaux_propres, cout_dette, wacc) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$exercice, $taux_sans_risque, $prime_risque, $beta, $cout_cp, $cout_dette, $wacc]);
    
    $message = "✅ Calcul du WACC effectué : " . number_format($wacc, 2) . "%";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-percent"></i> Coût du capital - WACC</h5>
                <small>Weighted Average Cost of Capital</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Structure financière</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><td>Capitaux propres</td><td class="text-end"><?= number_format($cp, 0, ',', ' ') ?> F</td><td class="text-end"><?= $total_financement > 0 ? number_format($cp / $total_financement * 100, 1) : 0 ?>%</td></tr>
                                    <tr><td>Dettes financières</td><td class="text-end"><?= number_format($dettes, 0, ',', ' ') ?> F</td><td class="text-end"><?= $total_financement > 0 ? number_format($dettes / $total_financement * 100, 1) : 0 ?>%</td></tr>
                                    <tr class="fw-bold"><td>TOTAL</td><td class="text-end"><?= number_format($total_financement, 0, ',', ' ') ?> F</td><td class="text-end">100%</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">💰 Calcul du WACC</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-4"><label>Taux sans risque (%)</label><input type="number" name="taux_sans_risque" class="form-control" value="3" step="0.1" required></div>
                                    <div class="col-md-4"><label>Prime de risque (%)</label><input type="number" name="prime_risque" class="form-control" value="8" step="0.1" required></div>
                                    <div class="col-md-4"><label>Bêta (β)</label><input type="number" name="beta" class="form-control" value="1.2" step="0.1" required></div>
                                    <div class="col-12 text-center"><button type="submit" class="btn-omega">Calculer le WACC</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formule du WACC -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">📐 Méthodologie de calcul</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>Formule du WACC :</strong><br>
                            WACC = (CP / Total) × Rcp + (D / Total) × Rd × (1 - IS)
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Coût des capitaux propres (MEDAF)</h6>
                                <p>Rcp = RF + β × (Rm - RF)</p>
                                <ul>
                                    <li>RF = Taux sans risque (Obligations d'État)</li>
                                    <li>β = Risque systématique de l'entreprise</li>
                                    <li>Rm - RF = Prime de risque du marché</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Coût de la dette</h6>
                                <p>Rd = Taux d'intérêt moyen × (1 - Taux IS)</p>
                                <p class="small text-muted">L'économie d'impôt réduit le coût réel de la dette</p>
                            </div>
                        </div>
                        <div class="alert alert-secondary mt-2">
                            <strong>📌 Interprétation :</strong> Un projet d'investissement est rentable si sa VAN est positive ou si son TRI est supérieur au WACC.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
