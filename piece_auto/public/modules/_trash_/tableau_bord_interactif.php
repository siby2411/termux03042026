<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Tableau de bord interactif – KPIs financiers";
include 'inc_navbar.php';
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
        .kpi-card { text-align: center; padding: 15px; border-radius: 12px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .kpi-value { font-size: 2rem; font-weight: bold; }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-speedometer2"></i> Tableau de bord interactif – KPIs financiers</h2>
                    <p>Indicateurs clés de performance, tendances, objectifs</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>📘 Objectif</strong><br>
                        Ce tableau de bord regroupe les principaux indicateurs financiers issus des différents modules.
                        Vous pouvez ajuster les hypothèses et visualiser l’impact sur la rentabilité et la liquidité.
                    </div>

                    <form method="post" id="kpiForm">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Chiffre d'affaires (k€)</label>
                                <input type="number" name="ca" class="form-control" value="2500" required>
                            </div>
                            <div class="col-md-3">
                                <label>Marge nette (%)</label>
                                <input type="number" step="0.5" name="marge_nette" class="form-control" value="8" required>
                            </div>
                            <div class="col-md-3">
                                <label>Capitaux propres (k€)</label>
                                <input type="number" name="cp" class="form-control" value="800" required>
                            </div>
                            <div class="col-md-3">
                                <label>Total actif (k€)</label>
                                <input type="number" name="actif" class="form-control" value="1800" required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label>Dettes financières (k€)</label>
                                <input type="number" name="dettes" class="form-control" value="600" required>
                            </div>
                            <div class="col-md-3">
                                <label>BFR (k€)</label>
                                <input type="number" name="bfr" class="form-control" value="450" required>
                            </div>
                            <div class="col-md-3">
                                <label>EBE (k€)</label>
                                <input type="number" name="ebe" class="form-control" value="350" required>
                            </div>
                            <div class="col-md-3">
                                <label>Objectif de ROE (%)</label>
                                <input type="number" step="1" name="objectif_roe" class="form-control" value="12">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="calculer" class="btn btn-success">Mettre à jour le tableau de bord</button>
                        </div>
                    </form>

                    <div id="kpiResult" class="mt-4"></div>
                    <div class="row mt-4">
                        <div class="col-md-6"><canvas id="kpiChart" width="400" height="200"></canvas></div>
                        <div class="col-md-6"><canvas id="trendChart" width="400" height="200"></canvas></div>
                    </div>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Modules associés</strong><br>
                        <a href="analyse_ratios.php" class="btn btn-sm btn-outline-primary">Analyse des ratios</a>
                        <a href="tableau_bord_ratios_avances.php" class="btn btn-sm btn-outline-primary">Ratios avancés (DuPont, Z‑Score)</a>
                        <a href="cockpit_final.php" class="btn btn-sm btn-outline-primary">Cockpit financier</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('kpiForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let ca = parseFloat(document.querySelector('input[name="ca"]').value);
        let marge_nette = parseFloat(document.querySelector('input[name="marge_nette"]').value) / 100;
        let cp = parseFloat(document.querySelector('input[name="cp"]').value);
        let actif = parseFloat(document.querySelector('input[name="actif"]').value);
        let dettes = parseFloat(document.querySelector('input[name="dettes"]').value);
        let bfr = parseFloat(document.querySelector('input[name="bfr"]').value);
        let ebe = parseFloat(document.querySelector('input[name="ebe"]').value);
        let objectif_roe = parseFloat(document.querySelector('input[name="objectif_roe"]').value);

        let resultat_net = ca * marge_nette;
        let roe = (resultat_net / cp) * 100;
        let roa = (ebe / actif) * 100;
        let liquidite_generale = actif / dettes;
        let autonomie_financiere = (cp / (cp + dettes)) * 100;
        let ebe_ca = (ebe / ca) * 100;

        let html = `<div class="row">
            <div class="col-md-3"><div class="kpi-card"><div class="kpi-value">${ca.toFixed(0)} k€</div><span>Chiffre d'affaires</span></div></div>
            <div class="col-md-3"><div class="kpi-card"><div class="kpi-value ${roe >= objectif_roe ? 'positive' : 'negative'}">${roe.toFixed(1)}%</div><span>ROE (objectif ${objectif_roe}%)</span></div></div>
            <div class="col-md-3"><div class="kpi-card"><div class="kpi-value">${ebe_ca.toFixed(1)}%</div><span>Marge EBITDA</span></div></div>
            <div class="col-md-3"><div class="kpi-card"><div class="kpi-value ${liquidite_generale >= 1.5 ? 'positive' : 'negative'}">${liquidite_generale.toFixed(2)}</div><span>Liquidité générale</span></div></div>
            <div class="col-md-3"><div class="kpi-card"><div class="kpi-value">${bfr.toFixed(0)} k€</div><span>Besoin en FDR</span></div></div>
            <div class="col-md-3"><div class="kpi-card"><div class="kpi-value ${autonomie_financiere >= 50 ? 'positive' : 'negative'}">${autonomie_financiere.toFixed(1)}%</div><span>Autonomie financière</span></div></div>
            <div class="col-md-3"><div class="kpi-card"><div class="kpi-value">${roa.toFixed(1)}%</div><span>ROA (rentabilité éco)</span></div></div>
            <div class="col-md-3"><div class="kpi-card"><div class="kpi-value">${resultat_net.toFixed(0)} k€</div><span>Résultat net</span></div></div>
        </div>`;
        document.getElementById('kpiResult').innerHTML = html;

        // Graphique radar des KPIs
        let ctxRadar = document.getElementById('kpiChart').getContext('2d');
        if (window.kpiChart) window.kpiChart.destroy();
        window.kpiChart = new Chart(ctxRadar, {
            type: 'radar',
            data: {
                labels: ['ROE', 'Marge EBITDA', 'Liquidité', 'Autonomie', 'ROA'],
                datasets: [{
                    label: 'Performance',
                    data: [roe, ebe_ca, liquidite_generale * 10, autonomie_financiere, roa],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: '#0d6efd'
                }]
            },
            options: { responsive: true, scales: { r: { beginAtZero: true, max: 100 } } }
        });

        // Graphique d'évolution (tendance)
        let ctxTrend = document.getElementById('trendChart').getContext('2d');
        if (window.trendChart) window.trendChart.destroy();
        window.trendChart = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: ['N-2', 'N-1', 'N (prévision)'],
                datasets: [
                    { label: 'ROE (%)', data: [10, 12, roe], borderColor: '#0d6efd' },
                    { label: 'Marge EBITDA (%)', data: [9, 11, ebe_ca], borderColor: '#28a745' }
                ]
            },
            options: { responsive: true }
        });
    });
</script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
