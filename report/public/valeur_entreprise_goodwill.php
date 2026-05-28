<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Valeur d’entreprise & Goodwill – Méthodes patrimoniale, DCF, Goodwill";
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
        .method-card { border-left: 5px solid #0d6efd; margin-bottom: 20px; }
        .formula { background: #f8f9fa; padding: 8px; border-radius: 6px; font-family: monospace; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-coin"></i> Valeur d’entreprise & Goodwill</h2>
                    <p>Méthodes patrimoniale, actualisation des flux (DCF), approche par les rendements (Goodwill)</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>📘 Concepts clés</strong><br>
                        • <strong>ANCC</strong> (Actif Net Comptable Corrigé) : valeur patrimoniale.<br>
                        • <strong>DCF</strong> (Discounted Cash Flows) : actualisation des flux futurs.<br>
                        • <strong>Goodwill</strong> = Valeur d’entreprise – ANCC. Il reflète la rentabilité future, la clientèle, la marque.<br>
                        • <strong>Méthode des praticiens</strong> = (ANCC + Valeur de rendement) / 2.
                    </div>

                    <form method="post" id="evalForm">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Actif net corrigé (ANCC) – k€</label>
                                <input type="number" name="ancc" class="form-control" value="800" required>
                            </div>
                            <div class="col-md-3">
                                <label>Capacité bénéficiaire nette (k€ / an)</label>
                                <input type="number" name="benefice" class="form-control" value="120" required>
                            </div>
                            <div class="col-md-3">
                                <label>Taux de capitalisation / WACC (%)</label>
                                <input type="number" step="0.5" name="taux" class="form-control" value="8" required>
                            </div>
                            <div class="col-md-3">
                                <label>Taux de croissance long terme (%)</label>
                                <input type="number" step="0.5" name="croissance" class="form-control" value="2" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Flux de trésorerie prévisionnels (k€) – séparés par des virgules (ex: 150,170,190,210)</label>
                                <input type="text" name="fcf" class="form-control" value="150,170,190,210" placeholder="150,170,190,210">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="calculer" class="btn btn-success">Évaluer l’entreprise</button>
                        </div>
                    </form>

                    <div id="resultats" class="mt-4"></div>
                    <div class="mt-4"><canvas id="valueChart" width="400" height="200"></canvas></div>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Outils associés</strong><br>
                        <a href="actualisation_cout_capital.php" class="btn btn-sm btn-outline-primary">VAN - TRI - IP</a>
                        <a href="analyse_ratios.php" class="btn btn-sm btn-outline-primary">Analyse des ratios</a>
                        <a href="evaluation_entreprise.php" class="btn btn-sm btn-outline-primary">Évaluation d’entreprise (simulateur)</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('evalForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let ancc = parseFloat(document.querySelector('input[name="ancc"]').value);
        let benefice = parseFloat(document.querySelector('input[name="benefice"]').value);
        let taux = parseFloat(document.querySelector('input[name="taux"]').value) / 100;
        let croissance = parseFloat(document.querySelector('input[name="croissance"]').value) / 100;
        let fcfString = document.querySelector('input[name="fcf"]').value;
        let fcf = fcfString.split(',').map(Number).filter(v => !isNaN(v));

        // Valeur de rendement par capitalisation
        let valeur_rendement = benefice / (taux - croissance);

        // Valeur des praticiens (moyenne ANCC / rendement)
        let valeur_praticiens = (ancc + valeur_rendement) / 2;

        // Goodwill
        let goodwill = valeur_praticiens - ancc;

        // DCF : actualisation des flux + valeur terminale
        let dcf = 0;
        for (let i = 0; i < fcf.length; i++) {
            dcf += fcf[i] / Math.pow(1 + taux, i + 1);
        }
        // Valeur terminale (croissance perpétuelle)
        let dernier_fcf = fcf[fcf.length - 1];
        let valeur_terminale = dernier_fcf * (1 + croissance) / (taux - croissance);
        dcf += valeur_terminale / Math.pow(1 + taux, fcf.length);
        let valeur_dcf = dcf; // DCF donne directement la valeur d'entreprise

        let html = `<div class="row">
            <div class="col-md-6"><div class="alert alert-info method-card">
                <strong>📊 Valeur patrimoniale (ANCC)</strong><br>${ancc.toFixed(0)} k€
            </div></div>
            <div class="col-md-6"><div class="alert alert-warning method-card">
                <strong>📈 Valeur de rendement</strong><br>${valeur_rendement.toFixed(0)} k€
            </div></div>
            <div class="col-md-6"><div class="alert alert-success method-card">
                <strong>⚖️ Méthode des praticiens</strong><br>${valeur_praticiens.toFixed(0)} k€<br>
                Goodwill = ${goodwill.toFixed(0)} k€
            </div></div>
            <div class="col-md-6"><div class="alert alert-primary method-card">
                <strong>💰 Actualisation des flux (DCF)</strong><br>${valeur_dcf.toFixed(0)} k€
            </div></div>
        </div>`;
        document.getElementById('resultats').innerHTML = html;

        // Graphique comparatif
        let ctx = document.getElementById('valueChart').getContext('2d');
        if (window.valueChart) window.valueChart.destroy();
        window.valueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['ANCC', 'Valeur rendement', 'Praticiens', 'DCF'],
                datasets: [{
                    label: 'Valeur (k€)',
                    data: [ancc, valeur_rendement, valeur_praticiens, valeur_dcf],
                    backgroundColor: ['#0d6efd', '#ffc107', '#28a745', '#17a2b8']
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    });
</script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
