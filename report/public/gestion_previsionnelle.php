<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Gestion prévisionnelle - Loi normale & BFR";
include 'inc_navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
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

                    <!-- Partie théorique -->
                    <h3><i class="bi bi-book"></i> 1. Rappels sur la loi normale</h3>
                    <p>La loi normale (ou loi de Laplace-Gauss) décrit de nombreux phénomènes financiers : ventes, BFR, erreurs de prévision…</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">Paramètres</div>
                                <div class="card-body">
                                    <ul>
                                        <li><strong>m (moyenne)</strong> : valeur centrale, symétrie de la courbe</li>
                                        <li><strong>σ (écart type)</strong> : dispersion autour de la moyenne</li>
                                        <li><strong>Loi normale N(m, σ²)</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">Propriétés</div>
                                <div class="card-body">
                                    <ul>
                                        <li>68% des valeurs dans [m-σ ; m+σ]</li>
                                        <li>95% dans [m-2σ ; m+2σ]</li>
                                        <li>99,7% dans [m-3σ ; m+3σ]</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-4"><i class="bi bi-calculator"></i> 2. Application au BFR (Besoin en Fonds de Roulement)</h3>
                    <p>Le BFR d’une entreprise peut être modélisé comme une variable aléatoire normale. On peut alors :</p>
                    <ul>
                        <li>Estimer la probabilité que le BFR dépasse un certain seuil (risque de sous-financement)</li>
                        <li>Déterminer le niveau de BFR à ne pas dépasser avec une probabilité donnée (ex : 95%)</li>
                        <li>Fixer une ligne de crédit adaptée</li>
                    </ul>
                    <div class="alert alert-info">
                        <strong>Formule de la fonction de répartition :</strong><br>
                        P(X ≤ x) = Φ((x – m)/σ)   où Φ est la fonction de répartition de la loi normale centrée réduite.
                    </div>

                    <!-- Simulateur -->
                    <h3 class="mt-4"><i class="bi bi-sliders"></i> 3. Simulateur interactif</h3>
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

                    <!-- Interprétation -->
                    <div class="alert alert-secondary mt-4">
                        <strong>💡 Interprétation pour la couverture du BFR :</strong><br>
                        Si la probabilité que le BFR dépasse le seuil est élevée (>20%), l’entreprise doit prévoir une ligne de crédit ou augmenter son fonds de roulement. 
                        On peut aussi déterminer le BFR maximal avec 95% de confiance : ce montant servira de base pour négocier un découvert autorisé.
                    </div>

                    <!-- Lien vers autres outils -->
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Calcul de la fonction de répartition normale (approximation de Abramowitz & Stegun)
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
        document.getElementById('resultats').innerHTML = '<div class="alert alert-danger">Veuillez entrer des valeurs valides (σ > 0).</div>';
        return;
    }
    var z = (seuil - m) / sigma;
    var proba_inf = normalCDF(z);
    var proba_sup = 1 - proba_inf;
    var html = '<div class="alert alert-info">';
    html += '🔹 P(BFR ≤ ' + seuil + ' k€) = ' + (proba_inf * 100).toFixed(2) + '%<br>';
    html += '🔸 P(BFR > ' + seuil + ' k€) = ' + (proba_sup * 100).toFixed(2) + '%<br>';
    if (proba_sup > 0.2) html += '<span class="text-danger">⚠️ Risque élevé de dépassement → besoin de financement supplémentaire.</span>';
    else html += '<span class="text-success">✅ Risque modéré, couverture BFR a priori adaptée.</span>';
    html += '</div>';
    document.getElementById('resultats').innerHTML = html;
    tracerCourbe(m, sigma, seuil);
}

function calculerInverse() {
    var m = parseFloat(document.getElementById('moyenne').value);
    var sigma = parseFloat(document.getElementById('sigma').value);
    var p = parseFloat(document.getElementById('proba_cible').value);
    if (isNaN(m) || isNaN(sigma) || sigma <= 0 || p <= 0 || p >= 1) {
        document.getElementById('resultats').innerHTML = '<div class="alert alert-danger">Valeurs invalides (σ > 0, 0 < p < 1).</div>';
        return;
    }
    // Approximation inverse pour la normale (fonction quantile)
    // Algorithme simple : recherche dichotomique
    var left = -1000, right = 1000;
    for (var i = 0; i < 100; i++) {
        var mid = (left + right) / 2;
        var fmid = normalCDF(mid);
        if (fmid < p) left = mid;
        else right = mid;
    }
    var z = (left + right) / 2;
    var seuil = m + z * sigma;
    var html = '<div class="alert alert-success">';
    html += '📌 Pour une probabilité de ' + (p * 100).toFixed(1) + '%, le BFR ne doit pas dépasser <strong>' + seuil.toFixed(0) + ' k€</strong>.<br>';
    html += '👉 Ce montant peut servir à calibrer une ligne de crédit ou la couverture du besoin.</div>';
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
            plugins: {
                tooltip: { callbacks: { label: (ctx) => 'f(x) = ' + ctx.raw.toFixed(4) } }
            },
            scales: { x: { title: { display: true, text: 'BFR (k€)' } } }
        }
    });
}

// Tracer courbe par défaut au chargement
window.onload = function() { calculer(); };
</script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
