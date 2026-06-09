<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Modèles d'évaluation d'entreprise";
$page_icon = "building";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];
$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des données financières
$ebitda = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END), 0) -
           COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END), 0)
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$ebitda->execute([$exercice]);
$ebitda = $ebitda->fetchColumn();

$ca = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 700 AND 799");
$ca->execute([$exercice]);
$ca = $ca->fetchColumn();

$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$cp = $capitaux_propres->fetchColumn();

$dettes = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id IN (164, 165, 166)");
$dettes->execute([$exercice]);
$dettes = $dettes->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $methode = $_POST['methode'];
    
    if ($methode === 'COMPARABLE') {
        $multiple_ebitda = (float)$_POST['multiple_ebitda'];
        $multiple_ca = (float)$_POST['multiple_ca'];
        
        $valeur_par_ebitda = $ebitda * $multiple_ebitda;
        $valeur_par_ca = $ca * $multiple_ca;
        $valeur_entreprise = ($valeur_par_ebitda + $valeur_par_ca) / 2;
        
        $resultats = [
            'methode' => 'Approche comparable',
            'valeur_ebitda' => $valeur_par_ebitda,
            'valeur_ca' => $valeur_par_ca,
            'valeur_entreprise' => $valeur_entreprise,
            'valeur_cp' => $valeur_entreprise - $dettes
        ];
    }
    elseif ($methode === 'ACTUARIELLE') {
        $taux_actualisation = (float)$_POST['taux_actualisation'];
        $croissance = (float)$_POST['croissance'];
        $cash_flow = (float)$_POST['cash_flow'];
        
        // Modèle de Gordon-Shapiro
        if ($taux_actualisation > $croissance) {
            $valeur_entreprise = $cash_flow / (($taux_actualisation - $croissance) / 100);
        } else {
            $valeur_entreprise = $cash_flow * (1 + $croissance/100) / (($taux_actualisation - $croissance) / 100);
        }
        
        $resultats = [
            'methode' => 'Approche actuarielle (DCF)',
            'valeur_entreprise' => $valeur_entreprise,
            'valeur_cp' => $valeur_entreprise - $dettes
        ];
    }
    elseif ($methode === 'PATRIMONIALE') {
        $actif_reel = (float)$_POST['actif_reel'];
        $actif_hors_bilan = (float)$_POST['actif_hors_bilan'];
        
        $valeur_entreprise = $actif_reel + $actif_hors_bilan;
        
        $resultats = [
            'methode' => 'Approche patrimoniale',
            'valeur_entreprise' => $valeur_entreprise,
            'valeur_cp' => $valeur_entreprise - $dettes
        ];
    }
    
    // Sauvegarde
    $stmt = $pdo->prepare("INSERT INTO EVALUATION_ENTREPRISE_COMPLETE (exercice, methode, valeur_entreprise, valeur_capitaux_propres, multiple_ebitda, multiple_ca, cash_flows_actualises) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$exercice, $methode, $resultats['valeur_entreprise'], $resultats['valeur_cp'], 
                    $_POST['multiple_ebitda'] ?? null, $_POST['multiple_ca'] ?? null, 
                    $_POST['cash_flow'] ?? null]);
    
    $message = "✅ Évaluation calculée - Valeur entreprise: " . number_format($resultats['valeur_entreprise'], 0, ',', ' ') . " FCFA";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-building"></i> Modèles d'évaluation d'entreprise</h5>
                <small>Approches comparative, actuarielle et patrimoniale</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Données de base (<?= $exercice ?>)</div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><td>EBITDA (Résultat d'exploitation)</td><td class="text-end fw-bold"><?= number_format($ebitda, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Chiffre d'affaires</td><td class="text-end fw-bold"><?= number_format($ca, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Capitaux propres</td><td class="text-end fw-bold"><?= number_format($cp, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Dettes financières</td><td class="text-end fw-bold"><?= number_format($dettes, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <ul class="nav nav-tabs" id="evalTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#comparable">📈 Approche comparative</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#actuarielle">📊 Approche actuarielle (DCF)</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#patrimoniale">🏛️ Approche patrimoniale</button></li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="comparable">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="methode" value="COMPARABLE">
                                    <div class="col-md-6"><label>Multiple EBITDA (secteur)</label><input type="number" name="multiple_ebitda" class="form-control" value="6" step="0.5" required></div>
                                    <div class="col-md-6"><label>Multiple CA (secteur)</label><input type="number" name="multiple_ca" class="form-control" value="1.2" step="0.1" required></div>
                                    <div class="col-12"><button type="submit" class="btn-omega w-100">Évaluer par comparables</button></div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="actuarielle">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="methode" value="ACTUARIELLE">
                                    <div class="col-md-4"><label>Cash-flow annuel (F)</label><input type="number" name="cash_flow" class="form-control" value="<?= number_format($ebitda, 0, '', '') ?>" required></div>
                                    <div class="col-md-4"><label>Taux actualisation (%)</label><input type="number" name="taux_actualisation" class="form-control" value="12" step="0.5" required></div>
                                    <div class="col-md-4"><label>Taux croissance (%)</label><input type="number" name="croissance" class="form-control" value="3" step="0.5" required></div>
                                    <div class="col-12"><button type="submit" class="btn-omega w-100">Évaluer par DCF (Gordon-Shapiro)</button></div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="patrimoniale">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="methode" value="PATRIMONIALE">
                                    <div class="col-md-6"><label>Actif net réel (F)</label><input type="number" name="actif_reel" class="form-control" value="<?= number_format($cp + $dettes, 0, '', '') ?>" required></div>
                                    <div class="col-md-6"><label>Actifs hors bilan (F)</label><input type="number" name="actif_hors_bilan" class="form-control" value="0" required></div>
                                    <div class="col-12"><button type="submit" class="btn-omega w-100">Évaluer par approche patrimoniale</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!empty($resultats)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">📊 Résultats de l'évaluation - <?= $resultats['methode'] ?></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-primary text-center">
                                    <h5>Valeur de l'entreprise</h5>
                                    <h3 class="text-primary"><?= number_format($resultats['valeur_entreprise'], 0, ',', ' ') ?> F</h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success text-center">
                                    <h5>Valeur des capitaux propres</h5>
                                    <h3 class="text-success"><?= number_format($resultats['valeur_cp'], 0, ',', ' ') ?> F</h3>
                                </div>
                            </div>
                        </div>
                        <?php if(isset($resultats['valeur_ebitda'])): ?>
                        <div class="alert alert-secondary">
                            <strong>Détail :</strong><br>
                            • Valeur par multiple EBITDA : <?= number_format($resultats['valeur_ebitda'], 0, ',', ' ') ?> F<br>
                            • Valeur par multiple CA : <?= number_format($resultats['valeur_ca'], 0, ',', ' ') ?> F
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
