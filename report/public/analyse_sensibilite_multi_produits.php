<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Analyse de sensibilité & rentabilité multi‑produits";
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
        .product-card { border-left: 5px solid #0d6efd; margin-bottom: 15px; }
        .result-badge { font-size: 1.2rem; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-sliders2"></i> Analyse de sensibilité & rentabilité multi‑produits</h2>
                    <p>Seuil de rentabilité, marge de sécurité, simulation de variation des prix et coûts</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>📘 Principes</strong><br>
                        Pour un produit unique : SR = Charges fixes / Taux de marge sur coût variable.<br>
                        Pour plusieurs produits, on raisonne en panier de produits (mix). L’analyse de sensibilité étudie l’impact d’une variation des paramètres (prix, coûts, volume).
                    </div>

                    <form method="post" id="sensiForm">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Charges fixes totales (k€)</label>
                                <input type="number" name="cf" class="form-control" value="500" required>
                            </div>
                            <div class="col-md-6">
                                <label>Nombre de produits</label>
                                <input type="number" name="nb_produits" id="nbProduits" class="form-control" value="2" min="1" max="5" required>
                            </div>
                        </div>

                        <div id="produitsContainer" class="mt-3"></div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label>Variation du volume des ventes (%)</label>
                                <input type="number" step="1" name="var_volume" class="form-control" value="0">
                            </div>
                            <div class="col-md-4">
                                <label>Variation du prix de vente (%)</label>
                                <input type="number" step="1" name="var_prix" class="form-control" value="0">
                            </div>
                            <div class="col-md-4">
                                <label>Variation des coûts variables (%)</label>
                                <input type="number" step="1" name="var_cv" class="form-control" value="0">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" onclick="genererProduits()">Configurer les produits</button>
                            <button type="submit" name="calculer" class="btn btn-success">Calculer & analyser</button>
                        </div>
                    </form>

                    <div id="resultats" class="mt-4"></div>
                    <div class="mt-4"><canvas id="sensiChart" width="400" height="200"></canvas></div>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Outils associés</strong><br>
                        <a href="seuil_rentabilite.php" class="btn btn-sm btn-outline-primary">Seuil de rentabilité (produit unique)</a>
                        <a href="analyse_scenarios_avancee.php" class="btn btn-sm btn-outline-primary">Analyse de scénarios (VAN/TRI)</a>
                        <a href="strategie_financiere_previsionnelle.php" class="btn btn-sm btn-outline-primary">Gestion prévisionnelle</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function genererProduits() {
        let nb = parseInt(document.getElementById('nbProduits').value);
        let html = '';
        for (let i = 1; i <= nb; i++) {
            html += `<div class="card product-card mb-2"><div class="card-header bg-light">Produit ${i}</div><div class="card-body row">
                        <div class="col-md-3"><label>Quantité vendue</label><input type="number" name="qte_${i}" class="form-control" value="1000"></div>
                        <div class="col-md-3"><label>Prix unitaire (€)</label><input type="number" name="prix_${i}" class="form-control" value="${i === 1 ? 100 : 80}"></div>
                        <div class="col-md-3"><label>Coût variable unitaire (€)</label><input type="number" name="cv_${i}" class="form-control" value="${i === 1 ? 60 : 50}"></div>
                        <div class="col-md-3"><label>Part dans le mix (%)</label><input type="number" step="1" name="mix_${i}" class="form-control" value="${i === 1 ? 60 : 40}"></div>
                    </div></div>`;
        }
        document.getElementById('produitsContainer').innerHTML = html;
    }

    window.onload = function() {
        genererProduits();
        document.getElementById('sensiForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let cf = parseFloat(document.querySelector('input[name="cf"]').value);
            let var_volume = parseFloat(document.querySelector('input[name="var_volume"]').value) / 100 + 1;
            let var_prix = parseFloat(document.querySelector('input[name="var_prix"]').value) / 100 + 1;
            let var_cv = parseFloat(document.querySelector('input[name="var_cv"]').value) / 100 + 1;
            let nb = parseInt(document.getElementById('nbProduits').value);

            let ca_total = 0, cv_total = 0;
            let details = [];

            for (let i = 1; i <= nb; i++) {
                let qte = parseFloat(document.querySelector(`input[name="qte_${i}"]`).value) * var_volume;
                let prix = parseFloat(document.querySelector(`input[name="prix_${i}"]`).value) * var_prix;
                let cv = parseFloat(document.querySelector(`input[name="cv_${i}"]`).value) * var_cv;
                let mix = parseFloat(document.querySelector(`input[name="mix_${i}"]`).value) / 100;

                let ca_prod = qte * prix;
                let cv_prod = qte * cv;
                ca_total += ca_prod;
                cv_total += cv_prod;
                details.push({nom: `Produit ${i}`, ca: ca_prod, cv: cv_prod, marge: ca_prod - cv_prod, mix: mix});
            }

            let marge_totale = ca_total - cv_total;
            let tmcv = ca_total > 0 ? marge_totale / ca_total : 0;
            let sr = tmcv > 0 ? cf / tmcv : Infinity;
            let resultat = marge_totale - cf;
            let marge_securite = ca_total - sr;
            let marge_securite_pct = ca_total > 0 ? (marge_securite / ca_total) * 100 : 0;

            let html = `<div class="alert alert-success">
                <strong>Résultats (après variations)</strong><br>
                CA total : ${ca_total.toFixed(0)} k€<br>
                Charges variables totales : ${cv_total.toFixed(0)} k€<br>
                Marge sur coûts variables : ${marge_totale.toFixed(0)} k€ (taux : ${(tmcv*100).toFixed(1)} %)<br>
                Seuil de rentabilité : ${sr.toFixed(0)} k€<br>
                Résultat : ${resultat.toFixed(0)} k€<br>
                Marge de sécurité : ${marge_securite.toFixed(0)} k€ (${marge_securite_pct.toFixed(1)} %)
            </div>`;

            html += '<h5>Détail par produit</h5><div class="table-responsive"><table class="table table-bordered">';
            html += '<thead class="table-dark"><tr><th>Produit</th><th>CA (k€)</th><th>CV (k€)</th><th>Marge (k€)</th><th>Mix</th></tr></thead><tbody>';
            for (let d of details) {
                html += `<tr><td>${d.nom}</td><td>${d.ca.toFixed(0)}</td><td>${d.cv.toFixed(0)}</td><td class="text-primary">${d.marge.toFixed(0)}</td><td>${(d.mix*100).toFixed(0)}%</td></tr>`;
            }
            html += '</tbody></table></div>';
            document.getElementById('resultats').innerHTML = html;

            // Graphique de sensibilité (CA, SR, résultat)
            let ctx = document.getElementById('sensiChart').getContext('2d');
            if (window.sensiChart) window.sensiChart.destroy();
            window.sensiChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Chiffre d\'affaires', 'Seuil rentabilité', 'Résultat'],
                    datasets: [{
                        label: 'Montant (k€)',
                        data: [ca_total, sr, resultat],
                        backgroundColor: ['#0d6efd', '#ffc107', '#28a745']
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
