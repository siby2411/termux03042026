<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Analyse structure financière & rentabilité";
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
        .ratio-card { border-left: 5px solid #0d6efd; margin-bottom: 20px; }
        .formula { background: #f8f9fa; padding: 8px; border-radius: 6px; font-family: monospace; }
        .good { color: #28a745; }
        .bad { color: #dc3545; }
        .medium { color: #ffc107; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-pie-chart"></i> Analyse de la structure financière & rentabilité</h2>
                    <p>Ratios d’endettement, levier, rentabilité commerciale, économique et financière</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>📘 Concepts clés</strong><br>
                        • <strong>Structure financière</strong> : fonds propres vs dettes (endettement, levier).<br>
                        • <strong>Rentabilité commerciale</strong> = marge nette (politique de prix).<br>
                        • <strong>Rentabilité économique (ROA)</strong> = résultat d’exploitation / actif total.<br>
                        • <strong>Rentabilité financière (ROE)</strong> = résultat net / capitaux propres.
                    </div>

                    <form method="post" id="ratioForm">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Capitaux propres (k€)</label>
                                <input type="number" name="cp" class="form-control" value="800" required>
                            </div>
                            <div class="col-md-3">
                                <label>Dettes totales (k€)</label>
                                <input type="number" name="dettes" class="form-control" value="1000" required>
                            </div>
                            <div class="col-md-3">
                                <label>Dettes à long terme (k€)</label>
                                <input type="number" name="dettes_lt" class="form-control" value="600" required>
                            </div>
                            <div class="col-md-3">
                                <label>Total actif (k€)</label>
                                <input type="number" name="actif" class="form-control" value="1800" required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label>Chiffre d'affaires (k€)</label>
                                <input type="number" name="ca" class="form-control" value="2500" required>
                            </div>
                            <div class="col-md-3">
                                <label>Résultat net (k€)</label>
                                <input type="number" name="rn" class="form-control" value="120" required>
                            </div>
                            <div class="col-md-3">
                                <label>EBE / Résultat d'exploitation (k€)</label>
                                <input type="number" name="ebe" class="form-control" value="350" required>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label><br>
                                <button type="submit" name="calculer" class="btn btn-success">Calculer les ratios</button>
                            </div>
                        </div>
                    </form>

                    <div id="resultats" class="mt-4"></div>
                    <div class="row mt-4">
                        <div class="col-md-6"><canvas id="structureChart" width="400" height="200"></canvas></div>
                        <div class="col-md-6"><canvas id="rentabiliteChart" width="400" height="200"></canvas></div>
                    </div>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Modules associés</strong><br>
                        <a href="analyse_ratios.php" class="btn btn-sm btn-outline-primary">Analyse des ratios</a>
                        <a href="tableau_bord_ratios_avances.php" class="btn btn-sm btn-outline-primary">Ratios avancés (DuPont, Z‑Score)</a>
                        <a href="bilan_fonctionnel.php" class="btn btn-sm btn-outline-primary">Bilan fonctionnel (FRNG/BFR)</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('ratioForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let cp = parseFloat(document.querySelector('input[name="cp"]').value);
        let dettes = parseFloat(document.querySelector('input[name="dettes"]').value);
        let dettes_lt = parseFloat(document.querySelector('input[name="dettes_lt"]').value);
        let actif = parseFloat(document.querySelector('input[name="actif"]').value);
        let ca = parseFloat(document.querySelector('input[name="ca"]').value);
        let rn = parseFloat(document.querySelector('input[name="rn"]').value);
        let ebe = parseFloat(document.querySelector('input[name="ebe"]').value);

        // Structure financière
        let passif_total = cp + dettes;
        let ratio_endettement = dettes / (cp + dettes); // Dettes / (CP + Dettes)
        let levier = dettes / cp;                       // Dettes / CP
        let capacite_endettement = cp / dettes;          // CP / Dettes (inverse)
        let endettement_lt = dettes_lt / (cp + dettes_lt);
        let autonomie = cp / (cp + dettes) * 100;        // %

        // Rentabilité
        let renta_commerciale = rn / ca * 100;           // Marge nette
        let renta_economique = ebe / actif * 100;        // ROA
        let renta_financiere = rn / cp * 100;            // ROE

        let interpretation_endettement = ratio_endettement < 0.5 ? "✅ Endettement maîtrisé" : (ratio_endettement < 0.66 ? "⚠️ Endettement modéré" : "❌ Endettement élevé");
        let interpretation_levier = levier < 1 ? "✅ Faible recours à la dette" : (levier < 2 ? "⚠️ Levier normal" : "❌ Levier très élevé");
        let interpretation_renta_com = renta_commerciale > 5 ? "✅ Bonne marge" : "⚠️ Marge faible";
        let interpretation_renta_eco = renta_economique > 8 ? "✅ Bonne rentabilité économique" : (renta_economique > 4 ? "⚠️ Rentabilité économique moyenne" : "❌ Rentabilité économique insuffisante");
        let interpretation_renta_fin = renta_financiere > 10 ? "✅ Bonne rentabilité financière" : (renta_financiere > 6 ? "⚠️ ROE moyen" : "❌ ROE faible");

        let html = `<div class="row">
            <div class="col-md-6"><div class="alert alert-primary ratio-card">
                <h5>📊 Structure financière</h5>
                <p>Ratio d'endettement (Dettes/Passif total) : ${(ratio_endettement*100).toFixed(1)}% → ${interpretation_endettement}<br>
                Levier financier (Dettes/CP) : ${levier.toFixed(2)} → ${interpretation_levier}<br>
                Capacité d'endettement (CP/Dettes) : ${capacite_endettement.toFixed(2)}<br>
                Autonomie financière : ${autonomie.toFixed(1)}%</p>
            </div></div>
            <div class="col-md-6"><div class="alert alert-success ratio-card">
                <h5>📈 Rentabilité</h5>
                <p>Rentabilité commerciale (marge nette) : ${renta_commerciale.toFixed(1)}% → ${interpretation_renta_com}<br>
                Rentabilité économique (ROA) : ${renta_economique.toFixed(1)}% → ${interpretation_renta_eco}<br>
                Rentabilité financière (ROE) : ${renta_financiere.toFixed(1)}% → ${interpretation_renta_fin}</p>
            </div></div>
        </div>`;
        document.getElementById('resultats').innerHTML = html;

        // Graphique structure (camembert)
        let ctxStruct = document.getElementById('structureChart').getContext('2d');
        if (window.structureChart) window.structureChart.destroy();
        window.structureChart = new Chart(ctxStruct, {
            type: 'doughnut',
            data: {
                labels: ['Capitaux propres', 'Dettes'],
                datasets: [{ data: [cp, dettes], backgroundColor: ['#28a745', '#dc3545'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } } }
        });

        // Graphique rentabilité (barres)
        let ctxRent = document.getElementById('rentabiliteChart').getContext('2d');
        if (window.rentabiliteChart) window.rentabiliteChart.destroy();
        window.rentabiliteChart = new Chart(ctxRent, {
            type: 'bar',
            data: {
                labels: ['Commerciale (marge)', 'Économique (ROA)', 'Financière (ROE)'],
                datasets: [{ label: 'Taux (%)', data: [renta_commerciale, renta_economique, renta_financiere], backgroundColor: '#0d6efd' }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, title: { display: true, text: '%' } } } }
        });
    });
</script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
