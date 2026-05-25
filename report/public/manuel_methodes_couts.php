<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel - Méthodes de calcul des coûts";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel des méthodes de calcul des coûts</h5>
                <small>Coûts complets, coûts variables, coûts directs, ABC</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Méthode des coûts complets (centres d'analyse)</li>
                        <li>Méthode des coûts variables (Direct Costing)</li>
                        <li>Méthode des coûts directs (Direct Costing évolué)</li>
                        <li>Méthode ABC (Activity-Based Costing)</li>
                        <li>Cas pratiques comparatifs</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">1. Méthode des coûts complets</div>
                    <div class="card-body">
                        <p><strong>📖 Principe :</strong> Imputer l'ensemble des charges (directes et indirectes) aux produits.</p>
                        <div class="alert alert-primary">
                            <strong>📊 Processus :</strong><br>
                            1. Regrouper les charges par centres d'analyse<br>
                            2. Répartir les centres auxiliaires vers les principaux<br>
                            3. Calculer le coût de l'unité d'œuvre<br>
                            4. Imputer aux produits selon leur consommation
                        </div>
                        <div class="alert alert-success">
                            <strong>✅ Avantage :</strong> Vision complète du coût de revient<br>
                            <strong>⚠️ Inconvénient :</strong> Répartition parfois arbitraire
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">2. Méthode des coûts variables (Direct Costing)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📊 Formule :</strong><br>
                            Marge sur coût variable = Chiffre d'affaires - Coûts variables<br>
                            Résultat = Marge SCV - Coûts fixes
                        </div>
                        <div class="alert alert-success">
                            <strong>✅ Avantage :</strong> Utile pour les décisions à court terme (seuil de rentabilité)<br>
                            <strong>⚠️ Inconvénient :</strong> Ignore les coûts fixes dans le coût de revient
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">3. Méthode des coûts directs (Direct Costing évolué)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📊 Principe :</strong> Distinction entre coûts fixes spécifiques et coûts fixes communs
                        </div>
                        <div class="alert alert-success">
                            <strong>✅ Avantage :</strong> Meilleure analyse de rentabilité par produit<br>
                            <strong>⚠️ Inconvénient :</strong> Difficulté d'affectation des coûts fixes communs
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">4. Méthode ABC (Activity-Based Costing)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📊 Processus :</strong><br>
                            1. Identifier les activités clés<br>
                            2. Déterminer les inducteurs de coûts<br>
                            3. Calculer le coût unitaire des inducteurs<br>
                            4. Imputer aux produits selon leur consommation d'activités
                        </div>
                        <div class="alert alert-success">
                            <strong>✅ Avantage :</strong> Méthode la plus précise, adaptée aux environnements complexes<br>
                            <strong>⚠️ Inconvénient :</strong> Lourdeur de mise en œuvre
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 - TABLEAU COMPARATIF -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">5. Tableau comparatif des méthodes</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Critère</th><th>Coûts complets</th><th>Direct Costing</th><th>ABC</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Charges prises en compte</td><td>Toutes</td><td>Variables uniquement</td><td>Par activité</td></tr>
                                    <tr><td>Précision</td><td>Moyenne</td><td>Faible</td><td>Élevée</td></tr>
                                    <tr><td>Complexité</td><td>Moyenne</td><td>Faible</td><td>Élevée</td></tr>
                                    <tr><td>Utilité décisionnelle</td><td>Long terme</td><td>Court terme</td><td>Stratégique</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- CAS PRATIQUE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">6. Cas pratique comparatif</div>
                    <div class="card-body">
                        <p><strong>📊 Données :</strong> Entreprise fabriquant 3 produits (P1, P2, P3)</p>
                        <ul>
                            <li>Coûts variables : P1=50€, P2=40€, P3=30€</li>
                            <li>Coûts fixes totaux : 100 000€</li>
                            <li>Activités : Approvisionnement, Production, Contrôle, Livraison</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <strong>📊 Résultats des différentes méthodes :</strong>
                            <table class="table table-sm mt-2">
                                <tr><th>Produit</th><th>Coûts complets</th><th>Direct Costing</th><th>ABC</th></tr>
                                <tr><td>P1</td><td>85€</td><td>50€</td><td>82€</td></tr>
                                <tr><td>P2</td><td>65€</td><td>40€</td><td>63€</td></tr>
                                <tr><td>P3</td><td>50€</td><td>30€</td><td>52€</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 ACCÈS AUX MODULES :</strong><br>
                    <a href="couts_complets.php" class="btn btn-sm btn-primary">📊 Coûts complets</a>
                    <a href="couts_variables.php" class="btn btn-sm btn-primary">📈 Coûts variables</a>
                    <a href="couts_directs.php" class="btn btn-sm btn-primary">🎯 Coûts directs</a>
                    <a href="couts_abc.php" class="btn btn-sm btn-primary">📊 Méthode ABC</a>
                    <a href="imputation_rationnelle.php" class="btn btn-sm btn-primary">⚙️ Imputation rationnelle</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
