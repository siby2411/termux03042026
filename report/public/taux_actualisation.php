<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Taux d'actualisation - Évaluation d'entreprise";
$page_icon = "percent";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];
$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des données financières
$beta_sectoriel = 1.2; // Valeur par défaut

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taux_sans_risque = (float)$_POST['taux_sans_risque'];
    $prime_risque_pays = (float)$_POST['prime_risque_pays'];
    $prime_risque_secteur = (float)$_POST['prime_risque_secteur'];
    $prime_taille = (float)$_POST['prime_taille'];
    $beta = (float)$_POST['beta'];
    
    // Calcul du taux d'actualisation (méthode MEDAF élargie)
    $taux_actualisation = $taux_sans_risque + ($beta * $prime_risque_secteur) + $prime_risque_pays + $prime_taille;
    
    $resultats = [
        'taux_sans_risque' => $taux_sans_risque,
        'prime_risque_pays' => $prime_risque_pays,
        'prime_risque_secteur' => $prime_risque_secteur,
        'prime_taille' => $prime_taille,
        'beta' => $beta,
        'taux_actualisation' => $taux_actualisation
    ];
    
    // Sauvegarde
    $stmt = $pdo->prepare("INSERT INTO TAUX_ACTUALISATION (exercice, taux_sans_risque, prime_risque_pays, prime_risque_secteur, prime_taille_entreprise, beta_entreprise, taux_actualisation) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$exercice, $taux_sans_risque, $prime_risque_pays, $prime_risque_secteur, $prime_taille, $beta, $taux_actualisation]);
    
    $message = "✅ Taux d'actualisation calculé : " . number_format($taux_actualisation, 2) . "%";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-percent"></i> Taux d'actualisation - Évaluation d'entreprise</h5>
                <small>Détermination du taux d'actualisation selon le modèle MEDAF élargi</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <strong>📖 Définition :</strong> Le taux d'actualisation est le rendement minimum exigé par les investisseurs. Il sert à actualiser les flux futurs pour déterminer la valeur actuelle d'une entreprise.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Paramètres du modèle</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6">
                                        <label>Taux sans risque (Rf) %</label>
                                        <input type="number" name="taux_sans_risque" class="form-control" value="3" step="0.5" required>
                                        <small class="text-muted">Obligations d'État à 10 ans</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Prime de risque pays %</label>
                                        <input type="number" name="prime_risque_pays" class="form-control" value="2" step="0.5" required>
                                        <small class="text-muted">Risque spécifique au pays (Sénégal/UEMOA)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Prime de risque secteur %</label>
                                        <input type="number" name="prime_risque_secteur" class="form-control" value="5" step="0.5" required>
                                        <small class="text-muted">Risque lié au secteur d'activité</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Prime de taille %</label>
                                        <input type="number" name="prime_taille" class="form-control" value="1.5" step="0.5" required>
                                        <small class="text-muted">Prime pour PME/ETI</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Bêta (β)</label>
                                        <input type="number" name="beta" class="form-control" value="1.2" step="0.1" required>
                                        <small class="text-muted">Risque systématique de l'entreprise</small>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-omega w-100">Calculer le taux d'actualisation</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if(!empty($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📊 Résultat du calcul</div>
                            <div class="card-body">
                                <div class="alert alert-primary">
                                    <strong>Formule :</strong> k = Rf + (β × Prime secteur) + Prime pays + Prime taille
                                </div>
                                <table class="table table-bordered">
                                    <tr><th>Paramètre</th><th>Valeur</th></tr>
                                    <tr><td>Taux sans risque (Rf)</td><td class="text-end"><?= number_format($resultats['taux_sans_risque'], 2) ?>%</td></tr>
                                    <tr><td>Prime de risque pays</td><td class="text-end">+ <?= number_format($resultats['prime_risque_pays'], 2) ?>%</td></tr>
                                    <tr><td>β × Prime secteur</td><td class="text-end">+ <?= number_format($resultats['beta'] * $resultats['prime_risque_secteur'], 2) ?>%</td></tr>
                                    <tr><td>Prime de taille</td><td class="text-end">+ <?= number_format($resultats['prime_taille'], 2) ?>%</td></tr>
                                    <tr class="table-primary fw-bold"><td>TAUX D'ACTUALISATION (k)</td><td class="text-end"><?= number_format($resultats['taux_actualisation'], 2) ?>%</td></tr>
                                </table>
                                
                                <div class="alert alert-secondary mt-2">
                                    <strong>💡 Interprétation :</strong><br>
                                    Un taux d'actualisation de <strong><?= number_format($resultats['taux_actualisation'], 2) ?>%</strong> signifie que les investisseurs exigent un rendement annuel de <?= number_format($resultats['taux_actualisation'], 2) ?>% pour investir dans cette entreprise.
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-info text-white">📖 Guide des paramètres</div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr><th>Paramètre</th><th>Définition</th><th>Valeurs typiques</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="fw-bold">Taux sans risque</td><td>Rendement des obligations d'État à 10 ans</td><td>3% - 5% (Sénégal)</td></tr>
                                <tr><td class="fw-bold">Prime de risque pays</td><td>Risque politique, économique, change</td><td>1% - 4% (UEMOA)</td></tr>
                                <tr><td class="fw-bold">Prime de risque secteur</td><td>Volatilité du secteur d'activité</td><td>3% - 8%</td></tr>
                                <tr><td class="fw-bold">Prime de taille</td><td>Risque supplémentaire pour PME</td><td>0% - 3%</td></tr>
                                <tr><td class="fw-bold">Bêta (β)</td><td>Sensibilité aux variations du marché</td><td>0.8 - 1.5</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
