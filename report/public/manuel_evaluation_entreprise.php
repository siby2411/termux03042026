<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel - Évaluation d'entreprise";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel d'évaluation d'entreprise</h5>
                <small>Taux d'actualisation, WACC, DCF, Gordon-Shapiro</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Le taux d'actualisation (MEDAF élargi)</li>
                        <li>Le Coût Moyen Pondéré du Capital (WACC)</li>
                        <li>La méthode DCF (Discounted Cash Flow)</li>
                        <li>Le modèle de Gordon-Shapiro</li>
                        <li>Cas pratique complet</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">1. Le taux d'actualisation</div>
                    <div class="card-body">
                        <p>Le taux d'actualisation représente le rendement minimum exigé par les investisseurs.</p>
                        <div class="alert alert-primary">
                            <strong>Formule MEDAF élargi :</strong><br>
                            k = Rf + (β × Prime secteur) + Prime pays + Prime taille
                        </div>
                        <table class="table table-bordered">
                            <tr><th>Paramètre</th><th>Définition</th><th>Exemple</th></tr>
                            <tr><td class="fw-bold">Rf</td><td>Taux sans risque (obligations d'État)</td><td>3%</td></tr>
                            <tr><td class="fw-bold">β</td><td>Risque systématique de l'entreprise</td><td>1,2</td></tr>
                            <tr><td class="fw-bold">Prime secteur</td><td>Risque lié au secteur d'activité</td><td>5%</td></tr>
                            <tr><td class="fw-bold">Prime pays</td><td>Risque spécifique au pays</td><td>2%</td></tr>
                            <tr><td class="fw-bold">Prime taille</td><td>Risque supplémentaire pour PME</td><td>1,5%</td></tr>
                            <tr class="table-primary"><td colspan="2">Taux d'actualisation</td><td>3% + 1,2×5% + 2% + 1,5% = 12,5%</td></tr>
                        </table>
                        <div class="alert alert-success">🌐 Module : <a href="taux_actualisation.php">taux_actualisation.php</a></div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">2. Le Coût Moyen Pondéré du Capital (WACC)</div>
                    <div class="card-body">
                        <p>Le WACC est le coût moyen des différentes sources de financement.</p>
                        <div class="alert alert-primary">
                            <strong>Formule :</strong> WACC = (E/V) × Re + (D/V) × Rd × (1 - Tc)
                        </div>
                        <ul>
                            <li><strong>E/V</strong> : Poids des capitaux propres</li>
                            <li><strong>D/V</strong> : Poids de la dette</li>
                            <li><strong>Re</strong> : Coût des capitaux propres</li>
                            <li><strong>Rd</strong> : Coût de la dette avant impôt</li>
                            <li><strong>Tc</strong> : Taux d'impôt sur les sociétés</li>
                        </ul>
                        <div class="alert alert-info">
                            <strong>📊 Exemple :</strong> Avec Re=15%, Rd=6%, E/V=60%, D/V=40%, IS=25%<br>
                            WACC = 60%×15% + 40%×6%×(1-0,25) = 9% + 1,8% = 10,8%
                        </div>
                        <div class="alert alert-success">🌐 Module : <a href="cout_moyen_capital.php">cout_moyen_capital.php</a></div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">3. Méthode DCF (Discounted Cash Flow)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>Formule :</strong> Valeur entreprise = Σ (FCFₜ / (1 + k)ᵗ) + VT / (1 + k)ⁿ
                        </div>
                        <p>Étapes à suivre :</p>
                        <ol>
                            <li>Prévoir les flux de trésorerie libres (FCF) sur 5-10 ans</li>
                            <li>Déterminer le taux d'actualisation (k)</li>
                            <li>Calculer la valeur terminale (modèle de Gordon-Shapiro)</li>
                            <li>Actualiser tous les flux</li>
                            <li>Soustraire la dette pour obtenir la valeur des capitaux propres</li>
                        </ol>
                        <div class="alert alert-success">🌐 Module : <a href="evaluation_globale.php">evaluation_globale.php</a></div>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">4. Modèle de Gordon-Shapiro</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>Formule :</strong> VT = FCFₙ₊₁ / (k - g)
                        </div>
                        <p>Utilisé pour calculer la valeur terminale d'une entreprise, en supposant une croissance stable à l'infini.</p>
                        <div class="alert alert-warning">
                            <strong>⚠️ Condition :</strong> k > g (taux d'actualisation > taux de croissance)
                        </div>
                        <div class="alert alert-success">🌐 Module : <a href="gordon_shapiro.php">gordon_shapiro.php</a></div>
                    </div>
                </div>

                <!-- CHAPITRE 5 - CAS PRATIQUE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">5. Cas pratique - Société OMEGA SARL</div>
                    <div class="card-body">
                        <p><strong>📊 Données :</strong></p>
                        <ul>
                            <li>EBITDA : 5 000 000 F</li>
                            <li>Capitaux propres : 20 000 000 F</li>
                            <li>Dettes : 10 000 000 F</li>
                            <li>Coût capitaux propres : 15%</li>
                            <li>Coût dette : 6%</li>
                            <li>Taux IS : 25%</li>
                            <li>Croissance prévue : 5% par an</li>
                        </ul>
                        
                        <p><strong>🔢 Calculs :</strong></p>
                        <ol>
                            <li>WACC = (20/30)×15% + (10/30)×6%×0,75 = 10% + 1,5% = 11,5%</li>
                            <li>Valeur entreprise (DCF) = Σ Flux actualisés à 11,5%</li>
                            <li>Valeur capitaux propres = Valeur entreprise - 10 000 000 F</li>
                        </ol>
                        
                        <div class="alert alert-success">
                            <strong>✅ Conclusion :</strong> La valeur de l'entreprise est évaluée à environ 45 000 000 F.
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 ACCÈS AUX MODULES :</strong><br>
                    <a href="taux_actualisation.php" class="btn btn-sm btn-primary">📊 Taux d'actualisation</a>
                    <a href="cout_moyen_capital.php" class="btn btn-sm btn-primary">💰 Coût du capital (WACC)</a>
                    <a href="evaluation_globale.php" class="btn btn-sm btn-primary">🏢 Évaluation globale</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
