<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Évaluation Financière - VAN / TRI / DCF";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$van_result = null;
$tri_result = null;

// Calcul VAN/TRI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $investissement = (float)$_POST['investissement'];
    $taux = (float)$_POST['taux_actualisation'];
    $projet_nom = trim($_POST['projet_nom']);
    
    // Récupération des flux
    $flux = [];
    for($i = 1; $i <= 10; $i++) {
        $flux[$i] = (float)($_POST["flux_$i"] ?? 0);
    }
    
    // Calcul VAN
    $van = -$investissement;
    for($i = 1; $i <= 10; $i++) {
        if($flux[$i] > 0) {
            $van += $flux[$i] / pow(1 + ($taux/100), $i);
        }
    }
    
    // Calcul TRI (méthode par itération)
    $tri = 0;
    $tri_test = 0;
    for($t = 1; $t <= 50; $t++) {
        $tri_test = $t;
        $van_test = -$investissement;
        for($i = 1; $i <= 10; $i++) {
            if($flux[$i] > 0) {
                $van_test += $flux[$i] / pow(1 + ($tri_test/100), $i);
            }
        }
        if($van_test <= 0 && $tri == 0) {
            $tri = $tri_test - 1;
            break;
        }
    }
    
    // Indice de rentabilité
    $total_flux = array_sum($flux);
    $indice_rentabilite = ($total_flux / $investissement) * 100;
    
    // Enregistrement en base
    try {
        $stmt = $pdo->prepare("INSERT INTO CALCULS_VAN_TRI (projet_nom, investissement_initial, duree_vie, taux_actualisation, van_calculee, tri_calcule, indice_rentabilite, date_calcul, details_calcul) VALUES (?, ?, 10, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$projet_nom, $investissement, $taux, $van, $tri, $indice_rentabilite, date('Y-m-d'), json_encode($flux)]);
        $message = "✅ Calcul effectué avec succès";
    } catch (Exception $e) {
        $message = "⚠️ " . $e->getMessage();
    }
    
    $van_result = $van;
    $tri_result = $tri;
}

// Récupération des projets existants
$projets = $pdo->query("SELECT * FROM CALCULS_VAN_TRI ORDER BY date_calcul DESC")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Évaluation Financière - VAN / TRI / DCF</h5>
                <small>Calcul de la Valeur Actuelle Nette et du Taux de Rentabilité Interne</small>
            </div>
            <div class="card-body">
                
                <!-- Guides explicatifs -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <i class="bi bi-calculator-fill fs-2"></i>
                                <h6>VAN (Valeur Actuelle Nette)</h6>
                                <small>Somme des flux actualisés - Investissement initial</small>
                                <p class="mt-2"><strong>Règle :</strong> VAN > 0 = Projet rentable</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <i class="bi bi-percent fs-2"></i>
                                <h6>TRI (Taux Rentabilité Interne)</h6>
                                <small>Taux pour lequel VAN = 0</small>
                                <p class="mt-2"><strong>Règle :</strong> TRI > Taux exigé = Rentable</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <i class="bi bi-cash-stack fs-2"></i>
                                <h6>DCF (Discounted Cash Flow)</h6>
                                <small>Actualisation des flux de trésorerie futurs</small>
                                <p class="mt-2"><strong>Formule :</strong> CF / (1 + r)^n</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire de calcul -->
                <div class="card bg-light mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-calculator"></i> Calculateur VAN / TRI / DCF
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label>Nom du projet</label>
                                <input type="text" name="projet_nom" class="form-control" placeholder="Ex: Extension usine, Nouveau produit" required>
                            </div>
                            <div class="col-md-3">
                                <label>Investissement initial (FCFA)</label>
                                <input type="number" name="investissement" class="form-control" step="100000" placeholder="0" required>
                            </div>
                            <div class="col-md-3">
                                <label>Taux d'actualisation (%)</label>
                                <input type="number" name="taux_actualisation" class="form-control" step="0.5" value="12" required>
                                <small>CMPC ou taux exigé par les actionnaires</small>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="fw-bold">Flux de trésorerie annuels (FCFA) :</label>
                            </div>
                            
                            <?php for($i = 1; $i <= 10; $i++): ?>
                            <div class="col-md-2">
                                <label>Année <?= $i ?></label>
                                <input type="number" name="flux_<?= $i ?>" class="form-control" step="100000" placeholder="0">
                            </div>
                            <?php endfor; ?>
                            
                            <div class="col-12 text-center">
                                <button type="submit" class="btn-omega">
                                    <i class="bi bi-calculator"></i> Calculer VAN / TRI
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Résultats du calcul -->
                <?php if($van_result !== null): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-trophy"></i> Résultats de l'évaluation
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Valeur Actuelle Nette (VAN)</h6>
                                        <h3 class="<?= $van_result >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($van_result, 0, ',', ' ') ?> F
                                        </h3>
                                        <small><?= $van_result >= 0 ? '✅ Projet rentable' : '❌ Projet non rentable' ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Taux de Rentabilité Interne (TRI)</h6>
                                        <h3><?= number_format($tri_result, 2) ?>%</h3>
                                        <small>TRI > Taux actualisation = Rentable</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Indice de Rentabilité</h6>
                                        <h3><?= number_format($indice_rentabilite ?? 0, 2) ?>%</h3>
                                        <small>IR > 100% = Rentable</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Historique des évaluations -->
                <h6><i class="bi bi-clock-history"></i> Projets évalués</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Date</th><th>Projet</th><th>Investissement</th><th>Taux</th><th>VAN</th><th>TRI</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($projets as $p): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($p['date_calcul'])) ?></td>
                                <td><?= htmlspecialchars($p['projet_nom']) ?></td>
                                <td class="text-end"><?= number_format($p['investissement_initial'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= $p['taux_actualisation'] ?>%</td>
                                <td class="text-end <?= $p['van_calculee'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($p['van_calculee'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= number_format($p['tri_calcule'] ?? 0, 2) ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Cas pratique -->
                <div class="alert alert-info mt-3">
                    <i class="bi bi-briefcase"></i>
                    <strong>CAS PRATIQUE :</strong><br>
                    Investissement : 100.000.000 FCFA<br>
                    Flux annuels : 25.000.000 FCFA pendant 5 ans<br>
                    Taux d'actualisation : 10%<br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="chargerExemple()">Charger cet exemple</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function chargerExemple() {
    document.querySelector('input[name="projet_nom"]').value = "Projet exemple - Extension usine";
    document.querySelector('input[name="investissement"]').value = "100000000";
    document.querySelector('input[name="taux_actualisation"]').value = "10";
    for(let i = 1; i <= 5; i++) {
        document.querySelector(`input[name="flux_${i}"]`).value = "25000000";
    }
}
</script>

<?php include 'inc_footer.php'; ?>
