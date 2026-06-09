<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Module d'audit & cartographie des risques - COSO II ERM";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_risque'])) {
    $processus_id = $_POST['processus_id'] ?: null;
    $description = trim($_POST['description']);
    $probabilite = (int)$_POST['probabilite'];
    $impact = (int)$_POST['impact'];
    $objectif_coso = $_POST['objectif_coso'];
    
    $stmt = $pdo->prepare("INSERT INTO risques (processus_id, description, probabilite, impact, objectif_coso) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$processus_id, $description, $probabilite, $impact, $objectif_coso]);
    $message_success = "✅ Risque enregistré avec succès.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .risk-critical { background-color: #fff5f5; }
        .risk-moderate { background-color: #fffaf0; }
        .risk-low { background-color: #f0fff4; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Carte principale -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-shield-check"></i> Module d'audit & cartographie des risques</h2>
                    <p>Conforme COSO II ERM – Évaluation, matriçage, heat map et recommandations</p>
                </div>
                <div class="card-body">

                    <!-- Message de succès -->
                    <?php if (isset($message_success)): ?>
                        <div class="alert alert-success"><?= $message_success ?></div>
                    <?php endif; ?>

                    <!-- ==================== CARTE 1 : FORMULAIRE ==================== -->
                    <div class="card mb-4">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-plus-circle"></i> 1. Nouveau risque
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Processus</label>
                                        <select name="processus_id" class="form-select">
                                            <option value="">-- Sélectionner --</option>
                                            <option value="1">Achats et approvisionnements</option>
                                            <option value="2">Ventes et recouvrement</option>
                                            <option value="3">Trésorerie et paiements</option>
                                            <option value="4">Comptabilité et clôture</option>
                                            <option value="5">Conformité fiscale</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Objectif COSO</label>
                                        <select name="objectif_coso" class="form-select">
                                            <option value="Stratégique">📈 Stratégique</option>
                                            <option value="Opérationnel">⚙️ Opérationnel</option>
                                            <option value="Fiabilité">📊 Fiabilité</option>
                                            <option value="Conformité">⚖️ Conformité</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description du risque</label>
                                        <textarea name="description" class="form-control" rows="2" required></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Probabilité (1-5)</label>
                                        <input type="number" name="probabilite" class="form-control" min="1" max="5" value="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Impact (1-5)</label>
                                        <input type="number" name="impact" class="form-control" min="1" max="5" value="1" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" name="ajouter_risque" class="btn btn-primary w-100"><i class="bi bi-save"></i> Enregistrer le risque</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php
                    // Récupération des risques
                    $risques = $pdo->query("SELECT r.*, p.nom as processus_nom FROM risques r LEFT JOIN processus p ON r.processus_id = p.id ORDER BY r.score DESC")->fetchAll();
                    ?>

                    <!-- ==================== CARTE 2 : REGISTRE DES RISQUES ==================== -->
                    <div class="card mb-4">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-list-ul"></i> 2. Registre des risques (<?= count($risques) ?>)
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr><th>Processus</th><th>Description</th><th>P</th><th>I</th><th>Score</th><th>Objectif</th><th>Criticité</th><th>Action</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($risques)): ?>
                                        <tr><td colspan="8" class="text-center">Aucun risque enregistré. Utilisez le formulaire ci-dessus.<?php else: ?>
                                        <?php foreach ($risques as $r): 
                                            $score = $r['score'];
                                            if ($score >= 16) { $criticite = "Majeur"; $badge = "danger"; }
                                            elseif ($score >= 9) { $criticite = "Modéré"; $badge = "warning"; }
                                            else { $criticite = "Faible"; $badge = "success"; }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['processus_nom'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($r['description']) ?></td>
                                            <td class="text-center"><?= $r['probabilite'] ?>/5</td>
                                            <td class="text-center"><?= $r['impact'] ?>/5</td>
                                            <td class="text-center fw-bold"><?= $score ?></td>
                                            <td><span class="badge bg-secondary"><?= $r['objectif_coso'] ?></span></td>
                                            <td><span class="badge bg-<?= $badge ?> p-2"><?= $criticite ?></span></td>
                                            <td><button class="btn btn-sm btn-outline-primary recom-btn" onclick="genererRecommandation(this)">📝 Recommander</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CARTE 3 : HEAT MAP ==================== -->
                    <div class="card mb-4">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-heat-map"></i> 3. Heat Map des risques
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="riskHeatMap" width="400" height="400" style="max-width:100%; height:auto;"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <strong>📌 Légende :</strong><br>
                                        <span class="badge bg-success">🟢 Vert</span> Score 1-8 : Risque acceptable<br>
                                        <span class="badge bg-warning text-dark">🟡 Jaune</span> Score 9-15 : Risque à surveiller<br>
                                        <span class="badge bg-danger">🔴 Rouge</span> Score 16-25 : Risque critique - Action immédiate
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CARTE 4 : KPIs ==================== -->
                    <div class="card mb-4">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-graph-up"></i> 4. Indicateurs clés (KPIs) – Scorecard
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4"><div class="card bg-light"><div class="card-body"><h5>📋 Conformité</h5><h3 class="text-success">94%</h3><small>Objectif: >90%</small></div></div></div>
                                <div class="col-md-4"><div class="card bg-light"><div class="card-body"><h5>⏱️ Délai clôture</h5><h3 class="text-warning">15 j</h3><small>Objectif: 10 j</small></div></div></div>
                                <div class="col-md-4"><div class="card bg-light"><div class="card-body"><h5>🔍 Anomalies</h5><h3 class="text-danger">8%</h3><small>Taux détection</small></div></div></div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CARTE 5 : RECOMMANDATIONS ==================== -->
                    <div class="card mb-4">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-file-text"></i> 5. Recommandations d'audit
                        </div>
                        <div class="card-body" id="recommandationsContainer">
                            <div class="alert alert-secondary">Cliquez sur "Recommander" sur un risque pour générer un plan d'action personnalisé.</div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Conseil d'expert :</strong> Un processus peut être efficace mais non conforme. Votre tableau de bord doit alerter sur tout manquement réglementaire.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function genererRecommandation(btn) {
    const recommandations = [
        "Mettre en place une séparation des tâches entre ordonnancement et paiement.",
        "Former le personnel aux procédures de contrôle interne.",
        "Automatiser les rapprochements bancaires quotidiens.",
        "Renforcer la revue des comptes avant clôture.",
        "Mettre en place un calendrier des déclarations fiscales."
    ];
    let random = Math.floor(Math.random() * recommandations.length);
    let priorite = random <= 1 ? "Haute" : (random <= 3 ? "Moyenne" : "Basse");
    document.getElementById('recommandationsContainer').innerHTML = `
        <div class="alert alert-success">
            <strong>📝 Recommandation générée :</strong><br>
            ${recommandations[random]}<br>
            <small>Priorité : ${priorite} | Responsable : DAF / Responsable processus</small>
            <hr><button class="btn btn-sm btn-outline-secondary mt-2" onclick="document.getElementById('recommandationsContainer').innerHTML='<div class=\"alert alert-secondary\">Cliquez sur \"Recommander\" pour générer un plan d\'action personnalisé.</div>'">Fermer</button>
        </div>
    `;
}

<?php
$points = [];
$stmt = $pdo->query("SELECT probabilite, impact, score FROM risques");
while ($row = $stmt->fetch()) {
    $points[] = ['x' => $row['probabilite'], 'y' => $row['impact'], 'score' => $row['score']];
}
$json_data = json_encode($points);
?>
const ctx = document.getElementById('riskHeatMap').getContext('2d');
new Chart(ctx, {
    type: 'scatter',
    data: {
        datasets: [{
            label: 'Risques identifiés',
            data: <?= $json_data ?>,
            backgroundColor: function(ctx) {
                let score = ctx.raw.score;
                if (score >= 16) return '#dc3545';
                if (score >= 9) return '#ffc107';
                return '#28a745';
            },
            pointRadius: 8,
            pointHoverRadius: 12
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: { title: { display: true, text: 'Probabilité' }, min: 0, max: 6, stepSize: 1 },
            y: { title: { display: true, text: 'Impact' }, min: 0, max: 6, stepSize: 1 }
        }
    }
});
</script>
<?php include 'inc_footer.php'; ?>
