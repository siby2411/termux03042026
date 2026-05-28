<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Tableau de bord – Ratios avancés (DuPont, Z‑Score, EVA)";
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
        .kpi-card { border-left: 5px solid #0d6efd; margin-bottom: 15px; }
        .interpretation { background: #f8f9fa; padding: 10px; border-radius: 8px; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-speedometer2"></i> Tableau de bord – Ratios financiers avancés</h2>
                    <p>Décomposition DuPont, Z‑Score d’Altman, EVA (Economic Value Added)</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>📘 Indicateurs clés</strong><br>
                        • <strong>DuPont</strong> : ROE = Marge nette × Rotation des actifs × Levier financier<br>
                        • <strong>Z‑Score</strong> : prédiction de défaillance (Altman). Formule pour sociétés cotées (Z>2,99 sain, Z<1,81 risque)<br>
                        • <strong>EVA</strong> : Rentabilité économique après coût du capital. EVA = NOPAT – (Capital investi × WACC)
                    </div>

                    <form method="post" id="ratiosForm">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Résultat net (k€)</label>
                                <input type="number" name="resultat_net" class="form-control" value="120">
                            </div>
                            <div class="col-md-3">
                                <label>Capitaux propres (k€)</label>
                                <input type="number" name="cp" class="form-control" value="800">
                            </div>
                            <div class="col-md-3">
                                <label>Total actif (k€)</label>
                                <input type="number" name="actif" class="form-control" value="1800">
                            </div>
                            <div class="col-md-3">
                                <label>Chiffre d'affaires (k€)</label>
                                <input type="number" name="ca" class="form-control" value="2500">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label>Résultat d'exploitation (EBE) (k€)</label>
                                <input type="number" name="ebe" class="form-control" value="350">
                            </div>
                            <div class="col-md-3">
                                <label>Capital investi (k€)</label>
                                <input type="number" name="capital_investi" class="form-control" value="1600">
                            </div>
                            <div class="col-md-3">
                                <label>WACC (%)</label>
                                <input type="number" step="0.1" name="wacc" class="form-control" value="8">
                            </div>
                            <div class="col-md-3">
                                <label>Impôt sur les sociétés (%)</label>
                                <input type="number" step="1" name="is" class="form-control" value="25">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>BFR (k€)</label>
                                <input type="number" name="bfr" class="form-control" value="450">
                            </div>
                            <div class="col-md-4">
                                <label>Actif circulant (k€)</label>
                                <input type="number" name="ac" class="form-control" value="1200">
                            </div>
                            <div class="col-md-4">
                                <label>Passif circulant (k€)</label>
                                <input type="number" name="pc" class="form-control" value="750">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="calculer" class="btn btn-success">Calculer les ratios avancés</button>
                        </div>
                    </form>

                    <div id="resultats" class="mt-4"></div>
                    <div class="mt-4"><canvas id="ratiosChart" width="400" height="200"></canvas></div>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Outils associés</strong><br>
                        <a href="analyse_ratios.php" class="btn btn-sm btn-outline-primary">Analyse des ratios de base</a>
                        <a href="bilan_fonctionnel.php" class="btn btn-sm btn-outline-primary">Bilan fonctionnel (FRNG/BFR)</a>
                        <a href="outil_diagnostic_af.php" class="btn btn-sm btn-outline-primary">EBE - EBITDA - CAF</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('ratiosForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let rn = parseFloat(document.querySelector('input[name="resultat_net"]').value);
        let cp = parseFloat(document.querySelector('input[name="cp"]').value);
        let actif = parseFloat(document.querySelector('input[name="actif"]').value);
        let ca = parseFloat(document.querySelector('input[name="ca"]').value);
        let ebe = parseFloat(document.querySelector('input[name="ebe"]').value);
        let capital_investi = parseFloat(document.querySelector('input[name="capital_investi"]').value);
        let wacc = parseFloat(document.querySelector('input[name="wacc"]').value) / 100;
        let is = parseFloat(document.querySelector('input[name="is"]').value) / 100;
        let bfr = parseFloat(document.querySelector('input[name="bfr"]').value);
        let ac = parseFloat(document.querySelector('input[name="ac"]').value);
        let pc = parseFloat(document.querySelector('input[name="pc"]').value);

        // DuPont
        let marge_nette = rn / ca;
        let rotation_actif = ca / actif;
        let levier = actif / cp;
        let roe = rn / cp;
        let roe_dupont = marge_nette * rotation_actif * levier;

        // Z-Score Altman (version société cotée)
        let x1 = (actif - pc) / actif;  // fonds de roulement / actif
        let x2 = (rn) / actif;          // résultat net / actif
        let x3 = ebe / actif;           // EBITDA / actif
        let x4 = cp / (actif - cp);     // capitaux propres / dettes
        let x5 = ca / actif;            // rotation actif
        let zscore = 1.2*x1 + 1.4*x2 + 3.3*x3 + 0.6*x4 + 1.0*x5;
        let zone_risque = (zscore < 1.81) ? '⚠️ Zone de risque élevé' : ((zscore > 2.99) ? '✅ Zone saine' : '🟡 Zone grise');

        // EVA (Economic Value Added)
        let nopat = ebe * (1 - is);    // hypothèse : EBE proche du résultat d'exploitation
        let eva = nopat - (capital_investi * wacc);
        let eva_texte = eva > 0 ? '✅ Création de valeur' : '❌ Destruction de valeur';

        let html = `<div class="alert alert-info kpi-card">
            <h5>📐 Décomposition DuPont</h5>
            <p>Marge nette : ${(marge_nette*100).toFixed(2)}%<br>
            Rotation des actifs : ${rotation_actif.toFixed(2)}<br>
            Levier financier : ${levier.toFixed(2)}<br>
            <strong>ROE (dupond) : ${(roe_dupont*100).toFixed(2)}%</strong> (ROE direct : ${(roe*100).toFixed(2)}%)</p>
        </div>`;

        html += `<div class="alert alert-warning kpi-card">
            <h5>⚠️ Z‑Score d'Altman (prédiction de défaillance)</h5>
            <p>Z-Score = ${zscore.toFixed(2)}<br>${zone_risque}</p>
        </div>`;

        html += `<div class="alert alert-success kpi-card">
            <h5>💰 EVA (Economic Value Added)</h5>
            <p>NOPAT = ${nopat.toFixed(0)} k€<br>
            Coût du capital (WACC) = ${(wacc*100).toFixed(1)}%<br>
            EVA = ${eva.toFixed(0)} k€ → ${eva_texte}</p>
        </div>`;

        document.getElementById('resultats').innerHTML = html;

        // Graphique des composantes
        let ctx = document.getElementById('ratiosChart').getContext('2d');
        if (window.ratiosChart) window.ratiosChart.destroy();
        window.ratiosChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['ROE (%)', 'Z-Score', 'EVA (k€)'],
                datasets: [{
                    label: 'Valeur',
                    data: [(roe*100), zscore, eva],
                    backgroundColor: ['#0d6efd', '#ffc107', '#28a745']
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    });
</script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
