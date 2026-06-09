<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel des modèles financiers";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel des modèles financiers</h5>
                <small>Modigliani-Miller, Gordon-Shapiro, MEDAF</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Le modèle MEDAF (Capital Asset Pricing Model)</li>
                        <li>Le modèle de Modigliani-Miller (1958-1963)</li>
                        <li>Le modèle de Gordon-Shapiro (Dividend Discount Model)</li>
                        <li>Le coût moyen pondéré du capital (WACC)</li>
                        <li>Cas pratique complet</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">1. Le modèle MEDAF (CAPM)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>Formule :</strong> E(Ri) = Rf + β × (Rm - Rf)
                        </div>
                        <table class="table table-bordered">
                            <tr><th>Paramètre</th><th>Définition</th><th>Exemple</th></tr>
                            <tr><td class="fw-bold">Rf</td><td>Taux sans risque (Obligations d'État)</td><td>3%</td></tr>
                            <tr><td class="fw-bold">β (Bêta)</td><td>Risque systématique de l'action</td><td>1.2</td></tr>
                            <tr><td class="fw-bold">Rm - Rf</td><td>Prime de risque du marché</td><td>8%</td></tr>
                            <tr><td class="fw-bold">E(Ri)</td><td>Rentabilité attendue de l'actif</td><td>3% + 1.2×8% = 12.6%</td></tr>
                        </table>
                        <div class="alert alert-secondary mt-2">
                            <strong>💡 Interprétation :</strong> Plus le bêta est élevé, plus l'action est risquée et plus la rentabilité exigée est forte.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">2. Modèle de Modigliani-Miller</div>
                    <div class="card-body">
                        <h6>📌 Premier théorème (1958) - En l'absence d'impôt :</h6>
                        <div class="alert alert-primary">Valeur de l'entreprise = indépendante de la structure financière</div>
                        
                        <h6 class="mt-3">📌 Second théorème (1963) - Avec impôt :</h6>
                        <div class="alert alert-primary">VL = VU + Tc × D</div>
                        <ul>
                            <li><strong>VL</strong> : Valeur de l'entreprise endettée</li>
                            <li><strong>VU</strong> : Valeur de l'entreprise non endettée</li>
                            <li><strong>Tc</strong> : Taux d'impôt sur les sociétés (25% au Sénégal)</li>
                            <li><strong>D</strong> : Montant de la dette</li>
                        </ul>
                        
                        <div class="alert alert-success">
                            <strong>✅ Enseignement clé :</strong> L'endettement crée de la valeur grâce à l'économie d'impôt sur les intérêts.<br>
                            <strong>📊 Exemple :</strong> Une dette de 10 000 000 F à 25% d'IS génère une économie de 2 500 000 F.
                        </div>
                        
                        <h6 class="mt-3">📉 Coût du capital selon MM :</h6>
                        <div class="alert alert-info">
                            WACC = (E/V) × Re + (D/V) × Rd × (1 - Tc)<br>
                            Le WACC diminue avec l'endettement grâce à l'économie d'impôt.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">3. Modèle de Gordon-Shapiro</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>Formule :</strong> P₀ = D₁ / (k - g)
                        </div>
                        <p>Où :</p>
                        <ul>
                            <li><strong>P₀</strong> : Valeur théorique de l'action</li>
                            <li><strong>D₁</strong> = D₀ × (1 + g) : Dividende futur</li>
                            <li><strong>k</strong> : Taux de rendement exigé par les actionnaires</li>
                            <li><strong>g</strong> : Taux de croissance annuel des dividendes</li>
                        </ul>
                        <div class="alert alert-warning">
                            <strong>⚠️ Condition de validité :</strong> k > g (sinon le modèle diverge)
                        </div>
                        <div class="alert alert-info">
                            <strong>📊 Application :</strong> Entreprises matures avec croissance stable des dividendes.<br>
                            <strong>📌 Exemple :</strong> D₀=500F, g=3%, k=12% → P₀ = 500×1.03/(0.12-0.03) = 5 722 F
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">4. Coût moyen pondéré du capital (WACC)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>Formule :</strong> WACC = (E/V) × Re + (D/V) × Rd × (1 - Tc)
                        </div>
                        <table class="table table-bordered">
                            <tr><th>Paramètre</th><th>Signification</th><th>Exemple</th></tr>
                            <tr><td class="fw-bold">E/V</td><td>Poids des capitaux propres</td><td>60%</td></tr>
                            <tr><td class="fw-bold">D/V</td><td>Poids de la dette</td><td>40%</td></tr>
                            <tr><td class="fw-bold">Re</td><td>Coût des capitaux propres</td><td>12%</td></tr>
                            <tr><td class="fw-bold">Rd</td><td>Coût de la dette</td><td>6%</td></tr>
                            <tr><td class="fw-bold">Tc</td><td>Taux IS</td><td>25%</td></tr>
                            <tr class="table-primary fw-bold"><td colspan="2">WACC</td><td>60%×12% + 40%×6%×0.75 = 9%</td></tr>
                        </table>
                        <div class="alert alert-secondary mt-2">
                            <strong>💡 Interprétation :</strong> Le WACC est le taux minimum que doit générer un projet pour être rentable.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 - CAS PRATIQUE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">5. Cas pratique synthétique</div>
                    <div class="card-body">
                        <p><strong>📊 Données de l'entreprise OMEGA SARL :</strong></p>
                        <ul>
                            <li>EBIT (Résultat d'exploitation) : 5 000 000 F</li>
                            <li>Capitaux propres : 20 000 000 F</li>
                            <li>Dettes financières : 10 000 000 F</li>
                            <li>Taux d'intérêt : 6%</li>
                            <li>Taux IS : 25%</li>
                            <li>Bêta (β) : 1.2</li>
                            <li>Prime de risque : 8%</li>
                            <li>Taux sans risque : 3%</li>
                            <li>Dividende par action : 500 F</li>
                            <li>Taux de croissance : 3%</li>
                        </ul>
                        
                        <p><strong>🔢 Calculs :</strong></p>
                        <ul>
                            <li><strong>Coût des capitaux propres (MEDAF)</strong> = 3% + 1.2×8% = 12.6%</li>
                            <li><strong>Coût de la dette après IS</strong> = 6% × (1-0.25) = 4.5%</li>
                            <li><strong>WACC</strong> = (20/30)×12.6% + (10/30)×4.5% = 8.4% + 1.5% = 9.9%</li>
                            <li><strong>Valeur selon Gordon-Shapiro</strong> = 500×1.03/(0.126-0.03) = 5 365 F/action</li>
                            <li><strong>Économie d'impôt (MM)</strong> = 10 000 000 × 25% = 2 500 000 F</li>
                        </ul>
                        
                        <div class="alert alert-success">
                            <strong>✅ Conclusion :</strong> Le coût du capital (WACC) est de 9.9%. Un projet d'investissement est rentable si son TRI > 9.9%.
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 ACCÈS AUX MODULES :</strong><br>
                    <a href="modigliani_miller.php" class="btn btn-sm btn-primary">📊 Modigliani-Miller</a>
                    <a href="gordon_shapiro.php" class="btn btn-sm btn-primary">📈 Gordon-Shapiro</a>
                    <a href="cout_capital.php" class="btn btn-sm btn-primary">💰 Coût du capital (WACC)</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
