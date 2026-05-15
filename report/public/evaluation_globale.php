<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Évaluation globale d'entreprise";
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

$dettes = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id IN (164, 165, 166, 401)");
$dettes->execute([$exercice]);
$dettes = $dettes->fetchColumn();

$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$cp = $capitaux_propres->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taux_actualisation = (float)$_POST['taux_actualisation'];
    $croissance = (float)$_POST['croissance_perpetuelle'];
    $dettes_saisies = (float)$_POST['dettes'];
    $nb_actions = (int)$_POST['nb_actions'];
    $flux_annee1 = (float)$_POST['flux_annee1'];
    $flux_annee2 = (float)$_POST['flux_annee2'];
    $flux_annee3 = (float)$_POST['flux_annee3'];
    $flux_annee4 = (float)$_POST['flux_annee4'];
    $flux_annee5 = (float)$_POST['flux_annee5'];
    
    // Calcul des flux actualisés
    $valeur_terminale = 0;
    if ($taux_actualisation > $croissance) {
        $valeur_terminale = $flux_annee5 * (1 + $croissance / 100) / (($taux_actualisation - $croissance) / 100);
    }
    
    $vae = 0;
    $vae += $flux_annee1 / pow(1 + $taux_actualisation/100, 1);
    $vae += $flux_annee2 / pow(1 + $taux_actualisation/100, 2);
    $vae += $flux_annee3 / pow(1 + $taux_actualisation/100, 3);
    $vae += $flux_annee4 / pow(1 + $taux_actualisation/100, 4);
    $vae += $flux_annee5 / pow(1 + $taux_actualisation/100, 5);
    $vae += $valeur_terminale / pow(1 + $taux_actualisation/100, 5);
    
    $valeur_capitaux_propres = $vae - $dettes_saisies;
    $valeur_action = $nb_actions > 0 ? $valeur_capitaux_propres / $nb_actions : 0;
    
    $resultats = [
        'vae' => $vae,
        'valeur_cp' => $valeur_capitaux_propres,
        'valeur_action' => $valeur_action,
        'valeur_terminale' => $valeur_terminale
    ];
    
    // Sauvegarde
    $stmt = $pdo->prepare("INSERT INTO EVALUATION_GLOBALE (exercice, ebitda, flux_libre_tresorerie, taux_actualisation, croissance_perpetuelle, valeur_entreprise, valeur_capitaux_propres, valeur_action, nombre_actions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$exercice, $ebitda, array_sum([$flux_annee1, $flux_annee2, $flux_annee3, $flux_annee4, $flux_annee5]), $taux_actualisation, $croissance, $vae, $valeur_capitaux_propres, $valeur_action, $nb_actions]);
    
    $message = "✅ Évaluation réalisée - Valeur entreprise : " . number_format($vae, 0, ',', ' ') . " FCFA";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-building"></i> Évaluation globale d'entreprise</h5>
                <small>Méthode des flux de trésorerie actualisés (DCF) - Modèle de Gordon-Shapiro</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Données de base (<?= $exercice ?>)</div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><th>Indicateur</th><th class="text-end">Valeur</th></tr>
                                    <tr><td>EBITDA</td><td class="text-end"><?= number_format($ebitda, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Capitaux propres</td><td class="text-end"><?= number_format($cp, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Dettes financières</td><td class="text-end"><?= number_format($dettes, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">⚙️ Paramètres d'évaluation</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6">
                                        <label>Taux d'actualisation (%)</label>
                                        <input type="number" name="taux_actualisation" class="form-control" value="12" step="0.5" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Croissance perpétuelle (%)</label>
                                        <input type="number" name="croissance_perpetuelle" class="form-control" value="3" step="0.5" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Dettes (F)</label>
                                        <input type="number" name="dettes" class="form-control" value="<?= $dettes ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Nombre d'actions</label>
                                        <input type="number" name="nb_actions" class="form-control" value="1000" required>
                                    </div>
                                    <div class="col-12"><h6>Flux de trésorerie annuels prévisionnels (F)</h6></div>
                                    <?php for($i=1; $i<=5; $i++): ?>
                                    <div class="col-md-2">
                                        <label>Année <?= $i ?></label>
                                        <input type="number" name="flux_annee<?= $i ?>" class="form-control" value="<?= number_format($ebitda * (1 + ($i-1)*0.05), 0, '', '') ?>" required>
                                    </div>
                                    <?php endfor; ?>
                                    <div class="col-12"><button type="submit" class="btn-omega w-100">Évaluer l'entreprise</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!empty($resultats)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">📊 Résultats de l'évaluation</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white text-center">
                                    <div class="card-body">
                                        <h6>Valeur d'entreprise (VAE)</h6>
                                        <h4><?= number_format($resultats['vae'], 0, ',', ' ') ?> F</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white text-center">
                                    <div class="card-body">
                                        <h6>Valeur des capitaux propres</h6>
                                        <h4><?= number_format($resultats['valeur_cp'], 0, ',', ' ') ?> F</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white text-center">
                                    <div class="card-body">
                                        <h6>Valeur par action</h6>
                                        <h4><?= number_format($resultats['valeur_action'], 0, ',', ' ') ?> F</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-secondary mt-3">
                            <strong>📖 Méthodologie :</strong><br>
                            Valeur d'entreprise = Σ Flux actualisés + Valeur terminale actualisée<br>
                            Valeur capitaux propres = Valeur entreprise - Dettes financières<br>
                            Valeur action = Valeur capitaux propres / Nombre d'actions
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
