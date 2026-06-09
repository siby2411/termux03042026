<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Analyse de scénarios (VAN/TRI)";
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
        .scenario-card { border-left: 5px solid; margin-bottom: 15px; }
        .scenario-optimiste { border-left-color: #28a745; }
        .scenario-pessimiste { border-left-color: #dc3545; }
        .scenario-realiste { border-left-color: #ffc107; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-diagram-3"></i> Analyse de scénarios – VAN / TRI</h2>
                    <p>Comparaison des scénarios optimiste, pessimiste et réaliste</p>
                </div>
                <div class="card-body">

                    <!-- Formulaire de base -->
                    <form method="post" id="scenarioForm">
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
                                <label>Durée (années)</label>
                                <input type="number" name="duree" id="duree" class="form-control" value="5" required>
                            </div>
                        </div>

                        <!-- Trois scénarios : optimiste, réaliste, pessimiste -->
                        <div id="scenariosContainer" class="mt-4">
                            <!-- Les champs seront générés dynamiquement -->
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" onclick="genererScenarios()">Générer les champs</button>
                            <button type="submit" name="calculer" class="btn btn-success">Comparer les scénarios</button>
                        </div>
                    </form>

                    <div id="resultats" class="mt-4"></div>
                    <div class="mt-4"><canvas id="vanChart" width="400" height="200"></canvas></div>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Outils associés</strong><br>
                        <a href="budget_previsionnel_van_tri.php" class="btn btn-sm btn-outline-primary">Budget prévisionnel – VAN / TRI</a>
                        <a href="gestion_previsionnelle.php" class="btn btn-sm btn-outline-primary">Gestion prévisionnelle (loi normale)</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function genererScenarios() {
        let duree = document.getElementById('duree').value;
        let html = '';
        let scenarios = [
            { nom: 'Optimiste', class: 'success', facteur: 1.2, color: '#28a745' },
            { nom: 'Réaliste', class: 'warning', facteur: 1.0, color: '#ffc107' },
            { nom: 'Pessimiste', class: 'danger', facteur: 0.8, color: '#dc3545' }
        ];
        for (let s of scenarios) {
            html += `<div class="card scenario-card scenario-${s.nom.toLowerCase()} mb-3"><div class="card-header bg-${s.class} text-white">Scénario ${s.nom}</div><div class="card-body row">`;
            for (let i = 1; i <= duree; i++) {
                let valeurParDefaut = (i === 1 ? 250 : 300) * s.facteur;
                html += `<div class="col-md-3 mb-2"><label>Année ${i}</label><input type="number" name="flux_${s.nom.toLowerCase()}_${i}" class="form-control" value="${valeurParDefaut.toFixed(0)}" required></div>`;
            }
            html += `</div></div>`;
        }
        document.getElementById('scenariosContainer').innerHTML = html;
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

    window.onload = function() {
        genererScenarios();
        document.getElementById('scenarioForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let invest = parseFloat(document.querySelector('input[name="invest"]').value);
            let taux = parseFloat(document.querySelector('input[name="taux"]').value);
            let duree = parseInt(document.getElementById('duree').value);
            let scenarios = ['optimiste', 'realiste', 'pessimiste'];
            let resultats = [];

            for (let s of scenarios) {
                let flux = [];
                for (let i = 1; i <= duree; i++) {
                    let val = parseFloat(document.querySelector(`input[name="flux_${s}_${i}"]`).value);
                    flux.push(isNaN(val) ? 0 : val);
                }
                let van = calculerVAN(flux, invest, taux);
                let tri = calculerTRI(flux, invest);
                resultats.push({ nom: s, van: van, tri: tri, flux: flux });
            }

            let html = '<div class="row">';
            for (let r of resultats) {
                let classe = (r.nom === 'optimiste') ? 'success' : (r.nom === 'pessimiste' ? 'danger' : 'warning');
                html += `<div class="col-md-4"><div class="alert alert-${classe}"><strong>Scénario ${r.nom.charAt(0).toUpperCase() + r.nom.slice(1)}</strong><br>VAN = ${r.van.toFixed(2)} k€<br>TRI = ${r.tri.toFixed(2)} %<br>${r.van > 0 ? '✅ Acceptable' : '❌ Non rentable'}</div></div>`;
            }
            html += '</div>';
            document.getElementById('resultats').innerHTML = html;

            // Graphique comparatif VAN
            let ctx = document.getElementById('vanChart').getContext('2d');
            if (window.vanChart) window.vanChart.destroy();
            window.vanChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: resultats.map(r => r.nom),
                    datasets: [{
                        label: 'VAN (k€)',
                        data: resultats.map(r => r.van),
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                    }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: false } } }
            });
        });
    };
</script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
