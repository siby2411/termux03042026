<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Formation - VAN / TRI / DCF";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Formation : VAN / TRI / DCF</h5>
                <small>Évaluation financière des projets d'investissement</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-question-circle"></i>
                    <strong>📖 Qu'est-ce que la VAN ?</strong>
                    <p>La Valeur Actuelle Nette est la somme des flux de trésorerie futurs actualisés, diminuée de l'investissement initial.</p>
                    <p class="mt-2"><strong>🔢 Formule :</strong> VAN = Σ (CF<sub>t</sub> / (1 + r)<sup>t</sup>) - I<sub>0</sub></p>
                    <ul>
                        <li>CF<sub>t</sub> = Flux de trésorerie à l'année t</li>
                        <li>r = Taux d'actualisation (CMPC ou taux exigé)</li>
                        <li>I<sub>0</sub> = Investissement initial</li>
                    </ul>
                </div>
                
                <div class="alert alert-success">
                    <i class="bi bi-percent"></i>
                    <strong>📖 Qu'est-ce que le TRI ?</strong>
                    <p>Le Taux de Rentabilité Interne est le taux pour lequel la VAN est nulle.</p>
                    <p><strong>🔢 Règle :</strong> TRI > Taux exigé → Projet rentable</p>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-cash-stack"></i>
                    <strong>📖 Qu'est-ce que le DCF (Discounted Cash Flow) ?</strong>
                    <p>Méthode d'évaluation basée sur l'actualisation des flux de trésorerie futurs.</p>
                    <p><strong>Formule :</strong> Valeur = Σ (FCF<sub>t</sub> / (1 + WACC)<sup>t</sup>)</p>
                </div>
                
                <!-- Cas pratique -->
                <div class="card bg-light mt-3">
                    <div class="card-header bg-secondary text-white">📋 CAS PRATIQUE DÉTAILLÉ</div>
                    <div class="card-body">
                        <h6>Projet d'investissement :</h6>
                        <ul>
                            <li>Investissement initial : 100.000.000 FCFA</li>
                            <li>Durée : 5 ans</li>
                            <li>Flux annuels : 30.000.000 FCFA</li>
                            <li>Taux d'actualisation : 12%</li>
                        </ul>
                        
                        <h6 class="mt-3">Calcul détaillé :</h6>
                        <table class="table table-bordered">
                            <tr><th>Année</th><th>Flux (FCFA)</th><th>Coef. actualisation (1/(1+0,12)^n)</th><th>Flux actualisé</th></tr>
                            <tr><td>1</td><td class="text-end">30.000.000</td><td class="text-center">0,8929</td><td class="text-end">26.786.000</td></tr>
                            <tr><td>2</td><td class="text-end">30.000.000</td><td class="text-center">0,7972</td><td class="text-end">23.916.000</td></tr>
                            <tr><td>3</td><td class="text-end">30.000.000</td><td class="text-center">0,7118</td><td class="text-end">21.354.000</td></tr>
                            <tr><td>4</td><td class="text-end">30.000.000</td><td class="text-center">0,6355</td><td class="text-end">19.065.000</td></tr>
                            <tr><td>5</td><td class="text-end">30.000.000</td><td class="text-center">0,5674</td><td class="text-end">17.022.000</td></tr>
                            <tr class="table-primary"><th>Total flux actualisés</th><td colspan="2"></td><td class="text-end">108.143.000</td></tr>
                            <tr><th>VAN = 108.143.000 - 100.000.000 = <span class="text-success">8.143.000 F</span></th><td colspan="2"></td><td class="text-end text-success">VAN POSITIVE</td></tr>
                        </table>
                        
                        <div class="alert alert-success">
                            ✅ Conclusion : Projet RENTABLE car VAN > 0 et TRI > 12%
                        </div>
                        
                        <a href="../evaluation_financiere.php" class="btn btn-primary">
                            <i class="bi bi-calculator"></i> Calculer votre VAN/TRI
                        </a>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../evaluation_financiere.php" class="btn btn-primary">Accéder au module</a>
                    <a href="../formations/index.php" class="btn btn-outline-secondary">← Retour</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
