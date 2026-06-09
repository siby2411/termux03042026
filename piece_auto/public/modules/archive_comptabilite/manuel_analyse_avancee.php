<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel d'analyse financière avancée";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel d'analyse financière avancée</h5>
                <small>Score, Effet de levier, BFR, Plan de financement</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Le score de rentabilité (méthode Altman)</li>
                        <li>L'effet de levier financier</li>
                        <li>Le besoin en fonds de roulement (BFR)</li>
                        <li>La stratégie financière et les bilans prévisionnels</li>
                        <li>Le plan de financement</li>
                        <li>Cas pratique complet</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">1. Le score de rentabilité (méthode Altman)</div>
                    <div class="card-body">
                        <p>Le Z-Score d'Altman est un indicateur prédictif de la faillite des entreprises.</p>
                        <div class="alert alert-primary">
                            <strong>📊 Formule :</strong> Z = 6.56×A + 3.26×B + 6.72×C + 1.05×D<br>
                            A = Capitaux propres / Actif total<br>
                            B = Résultat net / CA<br>
                            C = (Résultat + Amortissements) / Actif total<br>
                            D = Dettes / Capitaux propres
                        </div>
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="card bg-success text-white"><div class="card-body">> 2.9<br>Zone sûre</div></div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="card bg-warning text-dark"><div class="card-body">1.8 - 2.9<br>Zone grise</div></div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="card bg-danger text-white"><div class="card-body">1.1 - 1.8<br>Zone risque</div></div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="card bg-dark text-white"><div class="card-body">&lt; 1.1<br>Zone critique</div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">2. L'effet de levier financier</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📊 Formule :</strong> ROA = ROE + (ROE - i) × D/CP<br>
                            • ROA = Rentabilité financière (Résultat net / Capitaux propres)<br>
                            • ROE = Rentabilité économique (Résultat d'exploitation / Actif total)<br>
                            • i = Taux d'intérêt moyen de la dette<br>
                            • D/CP = Ratio d'endettement
                        </div>
                        <div class="alert alert-success">
                            ✅ Effet de levier POSITIF : ROE > i → l'endettement augmente la rentabilité
                        </div>
                        <div class="alert alert-danger">
                            ❌ Effet de levier NÉGATIF : ROE < i → l'endettement réduit la rentabilité (risque)
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">3. Le besoin en fonds de roulement (BFR)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📊 Formule :</strong> BFR = (Stocks + Créances) - Dettes fournisseurs
                        </div>
                        <div class="alert alert-info">
                            <strong>📈 Calcul prévisionnel :</strong> BFR prévu = CA prévu × (Taux moyen de rotation)
                        </div>
                        <p><strong>Seuils critiques :</strong></p>
                        <ul>
                            <li>BFR < 0 : Ressource dégagée par le cycle d'exploitation</li>
                            <li>BFR > 0 : Besoin de financement à couvrir</li>
                            <li>BFR > FRNG : Risque d'insolvabilité à court terme</li>
                        </ul>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">4. Le plan de financement</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">RESSOURCES</div>
                                    <div class="card-body">
                                        <ul>
                                            <li>Capacité d'autofinancement (CAF)</li>
                                            <li>Augmentation de capital</li>
                                            <li>Nouveaux emprunts</li>
                                            <li>Cessions d'actifs</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">EMPLOIS</div>
                                    <div class="card-body">
                                        <ul>
                                            <li>Investissements</li>
                                            <li>Variation du BFR</li>
                                            <li>Remboursement d'emprunts</li>
                                            <li>Dividendes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-primary mt-2">
                            <strong>🔗 Relation :</strong> Variation de trésorerie = Ressources - Emplois
                        </div>
                    </div>
                </div>

                <!-- CAS PRATIQUE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📋 Cas pratique synthétique</div>
                    <div class="card-body">
                        <p><strong>Données de l'entreprise OMEGA SARL (Exercice N) :</strong></p>
                        <ul>
                            <li>Chiffre d'affaires : 28 500 000 F</li>
                            <li>Résultat net : 2 570 000 F</li>
                            <li>Capitaux propres : 8 000 000 F</li>
                            <li>Actif total : 25 000 000 F</li>
                            <li>Dettes financières : 5 000 000 F</li>
                            <li>Charges financières : 500 000 F</li>
                        </ul>
                        
                        <p><strong>Résultats de l'analyse :</strong></p>
                        <table class="table table-bordered">
                            <tr><th>Indicateur</th><th>Valeur</th><th>Interprétation</th></tr>
                            <tr><td>Z-Score Altman</td><td><?= number_format(2.45, 2) ?></td><td>Zone grise - Surveillance nécessaire</td></tr>
                            <tr><td>Effet de levier</td><td><?= number_format(1.2, 2) ?>%</td><td>Positif - L'endettement est bénéfique</td></tr>
                            <tr><td>BFR prévisionnel N+1</td><td>8 550 000 F</td><td>Besoin à financer par ressources stables</td></tr>
                            <tr><td>Plan de financement</td><td>Dégagement net</td><td>Trésorerie positive prévue</td></tr>
                        </table>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <strong>🌐 ACCÈS AUX MODULES :</strong><br>
                    <a href="score_rentabilite.php" class="btn btn-sm btn-primary">📊 Score rentabilité</a>
                    <a href="effet_levier.php" class="btn btn-sm btn-primary">💰 Effet de levier</a>
                    <a href="plan_financement.php" class="btn btn-sm btn-primary">📈 Plan financement</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
