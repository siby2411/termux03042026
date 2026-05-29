<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Gestion prévisionnelle – Loi normale & BFR";
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
        .formula { background: #f8f9fa; padding: 10px; border-radius: 8px; font-family: monospace; }
        .courbe { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-graph-up"></i> Gestion prévisionnelle – Loi normale et couverture du BFR</h2>
                    <p>Application des méthodes statistiques (Gauss-Laplace) à la gestion financière</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>📘 Rappels : Loi normale N(m, σ²)</strong><br>
                        • 68% des valeurs dans [m-σ ; m+σ]<br>
                        • 95% dans [m-2σ ; m+2σ]<br>
                        • 99,7% dans [m-3σ ; m+3σ]
                    </div>

                    <div class="card bg-light">
                        <div class="card-body">
                            <form method="post" id="simulateur">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Moyenne du BFR (m) – k€</label>
                                        <input type="number" step="10" name="moyenne" id="moyenne" class="form-control" value="450" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Écart type (σ) – k€</label>
                                        <input type="number" step="5" name="sigma" id="sigma" class="form-control" value="80" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Seuil à analyser (S) – k€</label>
                                        <input type="number" step="10" name="seuil" id="seuil" class="form-control" value="550" required>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-primary" onclick="calculer()">Calculer la probabilité</button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-success" onclick="calculerInverse()">Seuil pour une probabilité donnée</button>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label>Probabilité cible (0 à 1)</label>
                                        <input type="number" step="0.01" id="proba_cible" class="form-control" value="0.95">
                                    </div>
                                </div>
                            </form>

                            <div id="resultats" class="mt-4"></div>
                            <div class="courbe mt-4">
                                <canvas id="gaussChart" width="500" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-secondary mt-4">
                        <strong>💡 Interprétation :</strong><br>
                        Si la probabilité que le BFR dépasse le seuil est élevée (>20%), l'entreprise doit prévoir une ligne de crédit ou augmenter son fonds de roulement.
                    </div>

                    <div class="mt-3">
                        <a href="bfr_previsionnel.php" class="btn btn-outline-primary">📊 BFR prévisionnel</a>
                        <a href="tresorerie_previsionnelle.php" class="btn btn-outline-primary">💵 Trésorerie prévisionnelle</a>
                        <a href="analyse_scenarios.php" class="btn btn-outline-primary">🎲 Analyse de scénarios</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function normalCDF(x) {
    var t = 1 / (1 + 0.2316419 * Math.abs(x));
    var d = 0.3989423 * Math.exp(-x * x / 2);
    var p = d * t * (0.3193815 + t * (-0.3565638 + t * (1.781478 + t * (-1.821256 + t * 1.330274))));
    if (x > 0) return 1 - p;
    else return p;
}

function calculer() {
    var m = parseFloat(document.getElementById('moyenne').value);
    var sigma = parseFloat(document.getElementById('sigma').value);
    var seuil = parseFloat(document.getElementById('seuil').value);
    if (isNaN(m) || isNaN(sigma) || sigma <= 0) {
        document.getElementById('resultats').innerHTML = '<div class="alert alert-danger">Valeurs invalides (σ > 0).</div>';
        return;
    }
    var z = (seuil - m) / sigma;
    var proba_inf = normalCDF(z);
    var proba_sup = 1 - proba_inf;
    var html = '<div class="alert alert-info">';
    html += '🔹 P(BFR ≤ ' + seuil + ' k€) = ' + (proba_inf * 100).toFixed(2) + '%<br>';
    html += '🔸 P(BFR > ' + seuil + ' k€) = ' + (proba_sup * 100).toFixed(2) + '%<br>';
    if (proba_sup > 0.2) html += '<span class="text-danger">⚠️ Risque élevé → besoin de financement supplémentaire.</span>';
    else html += '<span class="text-success">✅ Risque modéré, couverture BFR adaptée.</span>';
    html += '</div>';
    document.getElementById('resultats').innerHTML = html;
    tracerCourbe(m, sigma, seuil);
}

function calculerInverse() {
    var m = parseFloat(document.getElementById('moyenne').value);
    var sigma = parseFloat(document.getElementById('sigma').value);
    var p = parseFloat(document.getElementById('proba_cible').value);
    if (isNaN(m) || isNaN(sigma) || sigma <= 0 || p <= 0 || p >= 1) {
        document.getElementById('resultats').innerHTML = '<div class="alert alert-danger">Valeurs invalides (σ > 0, 0<p<1).</div>';
        return;
    }
    var left = -10, right = 10, z = 0;
    for (var i = 0; i < 100; i++) {
        var mid = (left + right) / 2;
        var fmid = normalCDF(mid);
        if (fmid < p) left = mid;
        else right = mid;
    }
    z = (left + right) / 2;
    var seuil = m + z * sigma;
    var html = '<div class="alert alert-success">';
    html += '📌 Pour une probabilité de ' + (p * 100).toFixed(1) + '%, le BFR ne doit pas dépasser <strong>' + seuil.toFixed(0) + ' k€</strong>.';
    html += '</div>';
    document.getElementById('resultats').innerHTML = html;
    tracerCourbe(m, sigma, seuil);
}

function tracerCourbe(m, sigma, seuil) {
    var ctx = document.getElementById('gaussChart').getContext('2d');
    var points = [];
    var minX = m - 4 * sigma;
    var maxX = m + 4 * sigma;
    var step = (maxX - minX) / 200;
    for (var x = minX; x <= maxX; x += step) {
        var y = (1 / (sigma * Math.sqrt(2 * Math.PI))) * Math.exp(-Math.pow(x - m, 2) / (2 * sigma * sigma));
        points.push({x: x, y: y});
    }
    var labels = points.map(p => p.x.toFixed(0));
    var data = points.map(p => p.y);
    if (window.myChart) window.myChart.destroy();
    window.myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Densité de probabilité',
                data: data,
                borderColor: 'blue',
                fill: true,
                tension: 0.1,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            plugins: { tooltip: { callbacks: { label: (ctx) => 'f(x) = ' + ctx.raw.toFixed(4) } } },
            scales: { x: { title: { display: true, text: 'BFR (k€)' } } }
        }
    });
}

window.onload = function() { calculer(); };
</script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
