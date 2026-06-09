<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Actualisation & coût du capital - VAN, TRI, IP, délai de récupération";
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
        .result-card { border-left: 5px solid #0d6efd; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-calculator"></i> Actualisation & coût du capital</h2>
                    <p>VAN, TRI, IP, délai de récupération – Influence du taux d’actualisation (WACC, MEDAF)</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>📘 Principes fondamentaux</strong><br>
                        • <strong>Taux d’actualisation</strong> = coût moyen pondéré du capital (WACC) ou taux de rendement exigé.<br>
                        • <strong>VAN (Valeur Actuelle Nette)</strong> : somme des flux actualisés – investissement initial. Acceptable si VAN > 0.<br>
                        • <strong>TRI (Taux de Rendement Interne)</strong> : taux qui annule la VAN. Acceptable si TRI > coût du capital.<br>
                        • <strong>IP (Indice de Profitabilité)</strong> = (VAN / Investissement) + 1. Projet rentable si IP > 1.<br>
                        • <strong>Délai de récupération (pay‑back)</strong> : temps nécessaire pour récupérer l’investissement.
                    </div>

                    <form method="post" id="actualisationForm">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Investissement initial (k€)</label>
                                <input type="number" name="invest" class="form-control" value="1000" step="10" required>
                            </div>
                            <div class="col-md-4">
                                <label>Taux d'actualisation / WACC (%)</label>
                                <input type="number" step="0.1" name="taux" class="form-control" value="8" required>
                            </div>
                            <div class="col-md-4">
                                <label>Durée du projet (années)</label>
                                <input type="number" name="duree" id="duree" class="form-control" value="5" required>
                            </div>
                        </div>

                        <div id="fluxContainer" class="mt-3"></div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" onclick="genererFlux()">Générer les flux</button>
                            <button type="submit" name="calculer" class="btn btn-success">Calculer les critères</button>
                        </div>
                    </form>

                    <div id="resultats" class="mt-4"></div>
                    <div class="mt-4"><canvas id="fluxChart" width="400" height="200"></canvas></div>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Outils associés</strong><br>
                        <a href="cout_capital.php" class="btn btn-sm btn-outline-primary">Coût du capital (WACC)</a>
                        <a href="budget_previsionnel_van_tri.php" class="btn btn-sm btn-outline-primary">Budget prévisionnel – VAN / TRI</a>
                        <a href="analyse_scenarios_avancee.php" class="btn btn-sm btn-outline-primary">Analyse de scénarios</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function genererFlux() {
        let duree = parseInt(document.getElementById('duree').value);
        let html = '<div class="card"><div class="card-header bg-secondary text-white">Flux de trésorerie nets annuels (k€)</div><div class="card-body row">';
        for (let i = 1; i <= duree; i++) {
            let valeurDefaut = (i === 1 ? 250 : 300);
            html += `<div class="col-md-3 mb-2"><label>Année ${i}</label><input type="number" name="flux_${i}" class="form-control" value="${valeurDefaut}" required></div>`;
        }
        html += '</div></div>';
        document.getElementById('fluxContainer').innerHTML = html;
    }

    function calculerVAN(flux, invest, taux) {
        let van = -invest;
        for (let i = 0; i < flux.length; i++) {
            van += flux[i] / Math.pow(1 + taux / 100, i + 1);
        }
        return van;
    }

    function calculerTRI(flux, invest) {
        let guess = 0.1;
        let van = 1;
        let iter = 0;
        let step = 0.05;
        while (Math.abs(van) > 0.01 && iter < 100) {
            van = -invest;
            for (let i = 0; i < flux.length; i++) {
                van += flux[i] / Math.pow(1 + guess, i + 1);
            }
            if (van > 0) guess += step;
            else guess -= step;
            step /= 2;
            iter++;
        }
        return guess * 100;
    }

    function calculerDelaiRecuperation(flux, invest) {
        let cumul = 0;
        for (let i = 0; i < flux.length; i++) {
            cumul += flux[i];
            if (cumul >= invest) {
                let reste = invest - (cumul - flux[i]);
                let fraction = reste / flux[i];
                return (i + 1) - 1 + fraction;
            }
        }
        return Infinity;
    }

    window.onload = function() {
        genererFlux();
        document.getElementById('actualisationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let invest = parseFloat(document.querySelector('input[name="invest"]').value);
            let taux = parseFloat(document.querySelector('input[name="taux"]').value);
            let duree = parseInt(document.getElementById('duree').value);
            let flux = [];
            for (let i = 1; i <= duree; i++) {
                let val = parseFloat(document.querySelector(`input[name="flux_${i}"]`).value);
                flux.push(isNaN(val) ? 0 : val);
            }

            let van = calculerVAN(flux, invest, taux);
            let tri = calculerTRI(flux, invest);
            let ip = (van / invest) + 1;
            let delai = calculerDelaiRecuperation(flux, invest);
            let acceptation = (van > 0) ? "✅ Projet acceptable" : "❌ Projet non rentable";

            let html = `<div class="alert alert-success result-card">
                <strong>📊 Résultats de l'actualisation</strong><br>
                - VAN = ${van.toFixed(2)} k€<br>
                - TRI = ${tri.toFixed(2)} %<br>
                - Indice de Profitabilité (IP) = ${ip.toFixed(2)}<br>
                - Délai de récupération = ${delai.toFixed(2)} années<br>
                ${acceptation}
            </div>`;
            document.getElementById('resultats').innerHTML = html;

            // Graphique des flux
            let ctx = document.getElementById('fluxChart').getContext('2d');
            if (window.myChart) window.myChart.destroy();
            window.myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: flux.map((_, idx) => 'Année ' + (idx + 1)),
                    datasets: [{
                        label: 'Flux nets (k€)',
                        data: flux,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)'
                    }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        });
    };
</script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
