<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pdo = getPDO();

// Récupérer les statistiques mensuelles depuis la table pré-calculée
$statsMensuelles = $pdo->query("SELECT * FROM statistiques_mensuelles ORDER BY annee DESC, mois DESC LIMIT 12")->fetchAll();

// Si la table est vide, on peut générer des stats à la volée (exemple)
if (empty($statsMensuelles)) {
    // Statistiques globales
    $totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $totalMedecins = $pdo->query("SELECT COUNT(*) FROM medecins_prescripteurs")->fetchColumn();
    $totalAnalyses = $pdo->query("SELECT COUNT(*) FROM analyses WHERE actif = 1")->fetchColumn();
    $totalPrelevements = $pdo->query("SELECT COUNT(*) FROM prelevements")->fetchColumn();
    $totalAnalysesRealisees = $pdo->query("SELECT COUNT(*) FROM analyses_realisees")->fetchColumn();
    $totalFactures = $pdo->query("SELECT COUNT(*) FROM factures")->fetchColumn();
    $caTotal = $pdo->query("SELECT SUM(total_ttc) FROM factures WHERE reglee = 1")->fetchColumn();
    $caTotal = $caTotal ?: 0;
    $topAnalyses = $pdo->query("SELECT a.nom, COUNT(ar.id) as nb 
                                FROM analyses_realisees ar 
                                JOIN analyses a ON ar.analyse_id = a.id 
                                GROUP BY ar.analyse_id 
                                ORDER BY nb DESC LIMIT 5")->fetchAll();
} else {
    // Calculer les totaux depuis les stats mensuelles
    $totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $totalMedecins = $pdo->query("SELECT COUNT(*) FROM medecins_prescripteurs")->fetchColumn();
    $totalAnalyses = $pdo->query("SELECT COUNT(*) FROM analyses WHERE actif = 1")->fetchColumn();
    $totalPrelevements = $pdo->query("SELECT COUNT(*) FROM prelevements")->fetchColumn();
    $totalAnalysesRealisees = $pdo->query("SELECT COUNT(*) FROM analyses_realisees")->fetchColumn();
    $totalFactures = $pdo->query("SELECT COUNT(*) FROM factures")->fetchColumn();
    $caTotal = $pdo->query("SELECT SUM(ca_total) FROM statistiques_mensuelles")->fetchColumn();
    $caTotal = $caTotal ?: 0;
    $topAnalyses = $pdo->query("SELECT a.nom, COUNT(ar.id) as nb 
                                FROM analyses_realisees ar 
                                JOIN analyses a ON ar.analyse_id = a.id 
                                GROUP BY ar.analyse_id 
                                ORDER BY nb DESC LIMIT 5")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques - Laboratoire Médical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php require_once 'includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Tableau de bord statistique</h2>
        
        <!-- Indicateurs clés -->
        <div class="row mt-4">
            <div class="col-md-3"><div class="card text-white bg-primary mb-3"><div class="card-body"><h5>Patients</h5><p class="display-6"><?= $totalPatients ?></p></div></div></div>
            <div class="col-md-3"><div class="card text-white bg-success mb-3"><div class="card-body"><h5>Médecins</h5><p class="display-6"><?= $totalMedecins ?></p></div></div></div>
            <div class="col-md-3"><div class="card text-white bg-info mb-3"><div class="card-body"><h5>Analyses actives</h5><p class="display-6"><?= $totalAnalyses ?></p></div></div></div>
            <div class="col-md-3"><div class="card text-white bg-warning mb-3"><div class="card-body"><h5>Prélèvements</h5><p class="display-6"><?= $totalPrelevements ?></p></div></div></div>
        </div>
        <div class="row">
            <div class="col-md-4"><div class="card text-white bg-danger mb-3"><div class="card-body"><h5>Analyses réalisées</h5><p class="display-6"><?= $totalAnalysesRealisees ?></p></div></div></div>
            <div class="col-md-4"><div class="card text-white bg-secondary mb-3"><div class="card-body"><h5>Factures</h5><p class="display-6"><?= $totalFactures ?></p></div></div></div>
            <div class="col-md-4"><div class="card text-white bg-dark mb-3"><div class="card-body"><h5>CA total (FCFA)</h5><p class="display-6"><?= formatMoney($caTotal) ?></p></div></div></div>
        </div>

        <!-- Graphiques -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Top 5 analyses les plus demandées</div>
                    <div class="card-body">
                        <canvas id="topAnalysesChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Évolution mensuelle du CA</div>
                    <div class="card-body">
                        <canvas id="caMensuelChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des statistiques mensuelles -->
        <div class="card mt-4">
            <div class="card-header">Statistiques mensuelles détaillées</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr><th>Mois/Année</th><th>Total analyses</th><th>Total patients</th><th>Total rendez-vous</th><th>CA mensuel (FCFA)</th>
                    </thead>
                    <tbody>
                        <?php if (!empty($statsMensuelles)): ?>
                            <?php foreach ($statsMensuelles as $s): ?>
                            <tr>
                                <td><?= sprintf('%02d/%d', $s['mois'], $s['annee']) ?></td>
                                <td><?= $s['total_analyses'] ?></td>
                                <td><?= $s['total_patients'] ?></td>
                                <td><?= $s['total_rendezvous'] ?></td>
                                <td><?= formatMoney($s['ca_total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">Aucune statistique mensuelle disponible.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Données pour le graphique des analyses les plus demandées
        const topAnalyses = <?= json_encode($topAnalyses) ?>;
        const labels = topAnalyses.map(a => a.nom);
        const data = topAnalyses.map(a => a.nb);
        const ctx = document.getElementById('topAnalysesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'Nombre d\'analyses', data: data, backgroundColor: '#3498db' }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // Graphique CA mensuel (si des stats mensuelles existent)
        <?php if (!empty($statsMensuelles)): ?>
        const caMensuel = <?= json_encode(array_reverse($statsMensuelles)) ?>;
        const moisLabels = caMensuel.map(s => `${s.mois}/${s.annee}`);
        const caData = caMensuel.map(s => s.ca_total);
        const ctx2 = document.getElementById('caMensuelChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: { labels: moisLabels, datasets: [{ label: 'Chiffre d\'affaires (FCFA)', data: caData, borderColor: '#2ecc71', fill: false }] },
            options: { responsive: true }
        });
        <?php else: ?>
        document.getElementById('caMensuelChart').getContext('2d').fillText('Aucune donnée mensuelle', 100, 150);
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
