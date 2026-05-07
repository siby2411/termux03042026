<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - Amortissements";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5><i class="bi bi-calculator-fill"></i> Module : Les Amortissements SYSCOHADA</h5>
                <small>Comprendre la dépréciation des immobilisations</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 DÉFINITION :</strong>
                    <p>L'amortissement est la constatation comptable de la perte de valeur d'une immobilisation due à l'usage, au temps ou à l'obsolescence.</p>
                </div>
                
                <!-- Principes généraux -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-primary text-white">
                                <h6>🔢 Méthodes d'amortissement</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>Linéaire</strong> : Annuité constante</li>
                                    <li><strong>Dégressif</strong> : Annuité décroissante</li>
                                    <li><strong>Exceptionnel</strong> : Dérogatoire</li>
                                </ul>
                                <div class="alert alert-secondary mt-2">
                                    <strong>📝 Écriture type :</strong><br>
                                    <code>681 - Dotations aux amortissements (Débit)</code><br>
                                    <code>28XX - Amortissements (Crédit)</code>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">
                                <h6>📊 Calcul de l'annuité linéaire</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Formule :</strong></p>
                                <code class="bg-white p-2 d-block mb-3 text-center">Annuité = (Valeur brute × Taux) / 100</code>
                                <p><strong>Cas pratique :</strong> Matériel informatique 2.000.000 F sur 5 ans</p>
                                <div class="alert alert-success">
                                    Taux = 100 / 5 = 20%<br>
                                    Annuité = 2.000.000 × 20% = 400.000 F/an
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tableau d'amortissement -->
                <h5 class="mt-4">📋 Exemple : Tableau d'amortissement linéaire</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Année</th>
                                <th>Valeur brute</th>
                                <th>Annuité</th>
                                <th>Amort. cumulé</th>
                                <th>Valeur nette (VNC)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="text-center">N</td><td class="text-end">2.000.000 F</td><td class="text-end">400.000 F</td><td class="text-end">400.000 F</td><td class="text-end">1.600.000 F</td></tr>
                            <tr><td class="text-center">N+1</td><td class="text-end">2.000.000 F</td><td class="text-end">400.000 F</td><td class="text-end">800.000 F</td><td class="text-end">1.200.000 F</td></tr>
                            <tr><td class="text-center">N+2</td><td class="text-end">2.000.000 F</td><td class="text-end">400.000 F</td><td class="text-end">1.200.000 F</td><td class="text-end">800.000 F</td></tr>
                            <tr><td class="text-center">N+3</td><td class="text-end">2.000.000 F</td><td class="text-end">400.000 F</td><td class="text-end">1.600.000 F</td><td class="text-end">400.000 F</td></tr>
                            <tr><td class="text-center">N+4</td><td class="text-end">2.000.000 F</td><td class="text-end">400.000 F</td><td class="text-end">2.000.000 F</td><td class="text-end">0 F</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>À retenir :</strong> L'amortissement commence à la date de mise en service, pas à la date d'acquisition.
                </div>
                
                <div class="text-center mt-4">
                    <a href="../amortissements_complet.php" class="btn btn-warning">Gérer vos amortissements →</a>
                    <a href="index.php" class="btn btn-outline-secondary">← Retour au didacticiel</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
