<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Analyse par scénarios financiers";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$simulation = null;

// Récupérer les scénarios existants
$sql = "SELECT s.*, COUNT(h.id) as nb_hypotheses FROM SCENARIOS_FINANCIERS s LEFT JOIN HYPOTHESES_SCENARIO h ON s.id = h.scenario_id GROUP BY s.id ORDER BY s.date_creation DESC";
$scenarios = $pdo->query($sql)->fetchAll();

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'creer_scenario') {
        $libelle = trim($_POST['libelle']);
        $description = trim($_POST['description']);
        
        $stmt = $pdo->prepare("INSERT INTO SCENARIOS_FINANCIERS (libelle, description) VALUES (?, ?)");
        $stmt->execute([$libelle, $description]);
        $scenario_id = $pdo->lastInsertId();
        
        $postes = ['CA', 'ACHATS', 'CHARGES'];
        foreach ($postes as $p) {
            $var = (float)($_POST["var_$p"] ?? 0);
            if ($var != 0) {
                $stmt2 = $pdo->prepare("INSERT INTO HYPOTHESES_SCENARIO (scenario_id, poste, variation_pourcent) VALUES (?, ?, ?)");
                $stmt2->execute([$scenario_id, $p, $var]);
            }
        }
        $message = "✅ Scénario '$libelle' créé avec succès.";
        header("Location: analyse_scenarios.php");
        exit();
    }
    
    if ($_POST['action'] === 'simuler') {
        $scenario_id = (int)$_POST['scenario_id'];
        $hyp = $pdo->prepare("SELECT poste, variation_pourcent FROM HYPOTHESES_SCENARIO WHERE scenario_id = ?");
        $hyp->execute([$scenario_id]);
        $hypotheses = $hyp->fetchAll();
        
        // Données réelles depuis les écritures comptables
        $ca_reel = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
        $achats_reel = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
        $charges_reel = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IN (641,651,652,653,681)")->fetchColumn();
        
        $ca_simule = $ca_reel;
        $achats_simule = $achats_reel;
        $charges_simule = $charges_reel;
        
        foreach ($hypotheses as $h) {
            if ($h['poste'] == 'CA') $ca_simule *= (1 + $h['variation_pourcent']/100);
            if ($h['poste'] == 'ACHATS') $achats_simule *= (1 + $h['variation_pourcent']/100);
            if ($h['poste'] == 'CHARGES') $charges_simule *= (1 + $h['variation_pourcent']/100);
        }
        
        $resultat_reel = $ca_reel - $achats_reel - $charges_reel;
        $resultat_simule = $ca_simule - $achats_simule - $charges_simule;
        
        $treso_actuelle = $pdo->query("SELECT COALESCE(SUM(CASE WHEN compte_debite_id=521 THEN montant ELSE 0 END),0) - COALESCE(SUM(CASE WHEN compte_credite_id=521 THEN montant ELSE 0 END),0) FROM ECRITURES_COMPTABLES")->fetchColumn();
        $treso_simulee = $treso_actuelle + $resultat_simule - $resultat_reel;
        $impact = $resultat_simule - $resultat_reel;
        
        $simulation = [
            'ca_simule' => $ca_simule,
            'achats_simule' => $achats_simule,
            'charges_simule' => $charges_simule,
            'resultat_simule' => $resultat_simule,
            'treso_simulee' => $treso_simulee,
            'impact' => $impact,
            'resultat_reel' => $resultat_reel
        ];
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Analyse par scénarios financiers</h5>
                <small>Anticipez l'impact de variations sur votre résultat et votre trésorerie</small>
            </div>
            <div class="card-body">
                
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <ul class="nav nav-tabs" id="scenarioTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nouveau">➕ Nouveau scénario</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#list">📋 Scénarios existants</button>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="nouveau">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="creer_scenario">
                                    <div class="col-md-6">
                                        <label>Libellé du scénario</label>
                                        <input type="text" name="libelle" class="form-control" placeholder="Ex: Croissance 10%" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Description</label>
                                        <input type="text" name="description" class="form-control" placeholder="Brève description">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Variation du CA (%)</label>
                                        <input type="number" name="var_CA" class="form-control" step="1" placeholder="+10 ou -5">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Variation des achats (%)</label>
                                        <input type="number" name="var_ACHATS" class="form-control" step="1">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Variation des charges (%)</label>
                                        <input type="number" name="var_CHARGES" class="form-control" step="1">
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn-omega">Créer le scénario</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="list">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Libellé</th><th>Date création</th><th>Hypothèses</th><th class="text-center">Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($scenarios)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Aucun scénario créé pour le moment.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($scenarios as $s): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($s['libelle']) ?> </td>
                                            <td class="text-center"><?= date('d/m/Y H:i', strtotime($s['date_creation'])) ?> </td>
                                            <td class="text-center"><?= $s['nb_hypotheses'] ?> variation(s)</td>
                                            <td class="text-center">
                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="action" value="simuler">
                                                    <input type="hidden" name="scenario_id" value="<?= $s['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-play-fill"></i> Simuler
                                                    </button>
                                                </form>
                                            </td>
                                         </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if($simulation !== null): ?>
                <div class="card mt-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h6><i class="bi bi-graph-up"></i> Résultat de la simulation</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-secondary">
                                    <strong>📊 Situation actuelle (réelle)</strong><br>
                                    CA : <?= number_format($simulation['resultat_reel'] + $simulation['achats_reel'] + $simulation['charges_reel'] ?? 0, 0, ',', ' ') ?> F<br>
                                    Résultat : <?= number_format($simulation['resultat_reel'], 0, ',', ' ') ?> F
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-primary">
                                    <strong>📈 Situation simulée</strong><br>
                                    CA : <?= number_format($simulation['ca_simule'], 0, ',', ' ') ?> F<br>
                                    Résultat : <?= number_format($simulation['resultat_simule'], 0, ',', ' ') ?> F
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center bg-info text-white">
                                    <div class="card-body">
                                        <h5><?= number_format($simulation['ca_simule'], 0, ',', ' ') ?> F</h5>
                                        <small>CA simulé</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-warning text-dark">
                                    <div class="card-body">
                                        <h5><?= number_format($simulation['charges_simule'], 0, ',', ' ') ?> F</h5>
                                        <small>Charges simulées</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-danger text-white">
                                    <div class="card-body">
                                        <h5><?= number_format($simulation['treso_simulee'], 0, ',', ' ') ?> F</h5>
                                        <small>Trésorerie simulée</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-success text-center mt-3">
                            <strong>📉 Impact du scénario sur le résultat :</strong>
                            <h3 class="<?= $simulation['impact'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($simulation['impact'], 0, ',', ' ') ?> F
                            </h3>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
