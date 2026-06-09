<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Budget prévisionnel - VAN / TRI";
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
                    <h2><i class="bi bi-calculator"></i> Budget prévisionnel – VAN / TRI</h2>
                    <p>Prévisions de ventes, charges, flux de trésorerie – Décision d’investissement</p>
                </div>
                <div class="card-body">

                    <!-- Partie théorique -->
                    <div class="alert alert-info">
                        <strong>📘 Principes</strong><br>
                        La <strong>VAN (Valeur Actuelle Nette)</strong> actualise les flux futurs à un taux donné. Un projet est acceptable si VAN > 0.<br>
                        Le <strong>TRI (Taux de Rendement Interne)</strong> est le taux qui annule la VAN. Il doit être supérieur au coût du capital.
                    </div>

                    <!-- Formulaire de saisie -->
                    <form method="post" id="previsionForm">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Investissement initial (k€)</label>
                                <input type="number" name="invest" class="form-control" value="1000" required>
                            </div>
                            <div class="col-md-4">
                                <label>Taux d'actualisation (%)</label>
                                <input type="number" step="0.1" name="taux" class="form-control" value="8" required>
                            </div>
                            <div class="col-md-4">
                                <label>Durée du projet (années)</label>
                                <input type="number" name="duree" id="duree" class="form-control" value="5" required>
                            </div>
                        </div>

                        <div id="fluxContainer" class="mt-3">
                            <!-- Les champs de flux seront générés dynamiquement -->
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" onclick="genererFlux()">Générer les champs</button>
                            <button type="submit" name="calculer" class="btn btn-success">Calculer VAN / TRI</button>
                        </div>
                    </form>

                    <div id="resultats" class="mt-4"></div>
                    <div class="mt-4"><canvas id="fluxChart" width="400" height="200"></canvas></div>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Liens utiles</strong><br>
                        <a href="flux_tresorerie_oec.php" class="btn btn-sm btn-outline-primary">Tableau des flux OEC</a>
                        <a href="seuil_rentabilite.php" class="btn btn-sm btn-outline-primary">Seuil de rentabilité</a>
                        <a href="analyse_scenarios.php" class="btn btn-sm btn-outline-primary">Analyse de scénarios</a>
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
        let duree = document.getElementById('duree').value;
        let html = '<div class="card"><div class="card-header bg-secondary text-white">Flux de trésorerie nets (k€)</div><div class="card-body row">';
        for (let i = 1; i <= duree; i++) {
            html += '<div class="col-md-3 mb-2"><label>Année ' + i + '</label><input type="number" name="flux_' + i + '" class="form-control" value="' + (i === 1 ? 250 : 300) + '" required></div>';
        }
        html += '</div></div>';
        document.getElementById('fluxContainer').innerHTML = html;
    }

    // Calcul VAN et TRI (approximation)
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

    window.onload = function() {
        genererFlux();
        document.getElementById('previsionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let invest = parseFloat(document.querySelector('input[name="invest"]').value);
            let taux = parseFloat(document.querySelector('input[name="taux"]').value);
            let duree = parseInt(document.getElementById('duree').value);
            let flux = [];
            for (let i = 1; i <= duree; i++) {
                let val = parseFloat(document.querySelector('input[name="flux_' + i + '"]').value);
                flux.push(isNaN(val) ? 0 : val);
            }
            let van = calculerVAN(flux, invest, taux);
            let tri = calculerTRI(flux, invest);
            let acceptation = (van > 0) ? "✅ Projet acceptable" : "❌ Projet non rentable";
            let html = '<div class="alert alert-success result-card"><strong>Résultats :</strong><br>' +
                'VAN = ' + van.toFixed(2) + ' k€<br>' +
                'TRI = ' + tri.toFixed(2) + ' %<br>' +
                acceptation + '</div>';
            document.getElementById('resultats').innerHTML = html;

            // Graphique
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
