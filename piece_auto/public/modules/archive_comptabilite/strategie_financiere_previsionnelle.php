<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Stratégie financière – Gestion prévisionnelle";
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
        .table-previsionnel { font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-calendar-check"></i> Stratégie financière – Gestion prévisionnelle</h2>
                    <p>Bilans prévisionnels, compte de résultat, plan de financement (horizon 3-5 ans)</p>
                </div>
                <div class="card-body">

                    <!-- Partie pédagogique -->
                    <div class="alert alert-info">
                        <strong>📘 Démarche de la stratégie financière</strong><br>
                        La gestion prévisionnelle s’appuie sur des hypothèses de croissance, d’investissements et de financement.<br>
                        Horizons : 3 à 5 ans (au-delà, les prévisions sont trop aléatoires).<br>
                        On construit des <strong>bilans prévisionnels</strong> et des <strong>comptes de résultat prévisionnels</strong>, puis on établit un <strong>plan de financement</strong>.
                    </div>

                    <!-- Formulaire des hypothèses -->
                    <form method="post" id="prevForm">
                        <h5>Hypothèses de base</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <label>CA année N (k€)</label>
                                <input type="number" name="ca_n" class="form-control" value="2500" required>
                            </div>
                            <div class="col-md-3">
                                <label>Taux de croissance annuel (%)</label>
                                <input type="number" step="0.5" name="taux_croissance" class="form-control" value="5" required>
                            </div>
                            <div class="col-md-3">
                                <label>Marge nette (%)</label>
                                <input type="number" step="0.5" name="marge_nette" class="form-control" value="8" required>
                            </div>
                            <div class="col-md-3">
                                <label>Horizon (années)</label>
                                <input type="number" name="horizon" id="horizon" class="form-control" value="3" min="1" max="5" required>
                            </div>
                        </div>

                        <h5 class="mt-3">Investissements prévisionnels (k€)</h5>
                        <div id="investContainer">
                            <!-- Généré dynamiquement -->
                        </div>

                        <h5 class="mt-3">Financements prévisionnels (emprunts, augmentation capital)</h5>
                        <div id="financementContainer">
                            <!-- Généré dynamiquement -->
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" onclick="genererAnnees()">Générer les années</button>
                            <button type="submit" name="calculer" class="btn btn-success">Établir les prévisions</button>
                        </div>
                    </form>

                    <div id="resultats" class="mt-4"></div>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Outils associés</strong><br>
                        <a href="budget_previsionnel_van_tri.php" class="btn btn-sm btn-outline-primary">Budget prévisionnel – VAN / TRI</a>
                        <a href="gestion_previsionnelle.php" class="btn btn-sm btn-outline-primary">Gestion prévisionnelle (loi normale)</a>
                        <a href="simulation_credit_emprunt.php" class="btn btn-sm btn-outline-primary">Simulation de crédit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function genererAnnees() {
        let horizon = parseInt(document.getElementById('horizon').value);
        let investHtml = '<div class="row">';
        let financeHtml = '<div class="row">';
        for (let i = 1; i <= horizon; i++) {
            investHtml += `<div class="col-md-3 mb-2"><label>Année N+${i}</label><input type="number" name="invest_${i}" class="form-control" value="${i === 1 ? 200 : 100}" step="10"></div>`;
            financeHtml += `<div class="col-md-3 mb-2"><label>Apports / emprunts N+${i}</label><input type="number" name="finance_${i}" class="form-control" value="${i === 1 ? 150 : 50}" step="10"></div>`;
        }
        investHtml += '</div>';
        financeHtml += '</div>';
        document.getElementById('investContainer').innerHTML = investHtml;
        document.getElementById('financementContainer').innerHTML = financeHtml;
    }

    window.onload = function() {
        genererAnnees();
        document.getElementById('prevForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let ca_n = parseFloat(document.querySelector('input[name="ca_n"]').value);
            let taux_croissance = parseFloat(document.querySelector('input[name="taux_croissance"]').value) / 100;
            let marge_nette = parseFloat(document.querySelector('input[name="marge_nette"]').value) / 100;
            let horizon = parseInt(document.getElementById('horizon').value);

            let invest = [];
            let finance = [];
            for (let i = 1; i <= horizon; i++) {
                let inv = parseFloat(document.querySelector(`input[name="invest_${i}"]`).value);
                let fin = parseFloat(document.querySelector(`input[name="finance_${i}"]`).value);
                invest.push(isNaN(inv) ? 0 : inv);
                finance.push(isNaN(fin) ? 0 : fin);
            }

            let ca = [ca_n];
            let resultat_net = [ca_n * marge_nette];
            let besoin_financement = [];
            let tresorerie_fin = [];

            for (let i = 1; i <= horizon; i++) {
                let ca_next = ca[i-1] * (1 + taux_croissance);
                ca.push(ca_next);
                let rn = ca_next * marge_nette;
                resultat_net.push(rn);
                let bfr = ca_next * 0.2; // hypothèse BFR = 20% du CA
                let besoin = bfr - (ca[i-1] * 0.2);
                besoin_financement.push(besoin > 0 ? besoin : 0);
                let treso = invest[i-1] + besoin_financement[i-1] - finance[i-1] - rn;
                tresorerie_fin.push(treso);
            }

            // Construction du tableau des résultats
            let html = '<h5>Tableau prévisionnel (k€)</h5><div class="table-responsive"><table class="table table-bordered table-previsionnel">';
            html += '<thead class="table-dark"><tr><th>Année</th><th>CA</th><th>Résultat net</th><th>Investissements</th><th>Financements</th><th>Variation BFR</th><th>Variation trésorerie</th></tr></thead><tbody>';
            for (let i = 0; i <= horizon; i++) {
                let annee = i === 0 ? 'N' : 'N+' + i;
                let ca_val = ca[i].toFixed(0);
                let rn_val = resultat_net[i].toFixed(0);
                let inv_val = i === 0 ? '-' : invest[i-1].toFixed(0);
                let fin_val = i === 0 ? '-' : finance[i-1].toFixed(0);
                let bfr_val = i === 0 ? '-' : besoin_financement[i-1].toFixed(0);
                let treso_val = i === 0 ? '-' : tresorerie_fin[i-1].toFixed(0);
                html += `<tr><td>${annee}</td><td>${ca_val}</td><td>${rn_val}</td><td>${inv_val}</td><td>${fin_val}</td><td>${bfr_val}</td><td class="${treso_val < 0 ? 'text-danger' : 'text-success'}">${treso_val}</td></tr>`;
            }
            html += '</tbody></table></div>';
            html += '<div class="alert alert-info mt-3"><strong>💡 Analyse :</strong> Une variation de trésorerie négative signifie un besoin de financement. Le plan de financement doit couvrir les investissements et la hausse du BFR.</div>';
            document.getElementById('resultats').innerHTML = html;
        });
    };
</script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
