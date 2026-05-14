<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel d'analyse financière";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel d'analyse financière</h5>
                <small>Seuil de rentabilité, EBE, CAF, Levier opérationnel</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Le seuil de rentabilité (point mort)</li>
                        <li>La marge de sécurité et l'indice de sécurité</li>
                        <li>L'Excédent Brut d'Exploitation (EBE)</li>
                        <li>La Capacité d'Autofinancement (CAF)</li>
                        <li>Le levier opérationnel</li>
                        <li>L'analyse en avenir aléatoire</li>
                        <li>Cas pratique société Générale</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 1. Le seuil de rentabilité</div>
                    <div class="card-body">
                        <p>Le <strong>seuil de rentabilité</strong> (ou point mort) est le niveau d'activité pour lequel l'entreprise ne réalise ni bénéfice ni perte.</p>
                        <div class="alert alert-success">
                            <strong>📊 Formule :</strong><br>
                            Seuil de rentabilité (CA) = Coûts fixes / Taux de marge sur coût variable
                        </div>
                        <div class="alert alert-light">
                            <strong>📝 Exemple :</strong><br>
                            Coûts fixes = 2 500 000 F<br>
                            Marge sur CV = 40%<br>
                            Seuil = 2 500 000 / 0,40 = <strong>6 250 000 F</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 2. Marge et indice de sécurité</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-success text-white">Marge de sécurité</div>
                                    <div class="card-body">
                                        <strong>Formule :</strong> CA réel - Seuil de rentabilité<br>
                                        <strong>Interprétation :</strong> Baisse d'activité supportable avant perte.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-info text-white">Indice de sécurité</div>
                                    <div class="card-body">
                                        <strong>Formule :</strong> Marge sécurité / CA réel × 100<br>
                                        <strong>Seuil :</strong> > 30% = confortable, 10-30% = acceptable, < 10% = fragile
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 3. Excédent Brut d'Exploitation (EBE)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📊 Formule EBE :</strong><br>
                            EBE = Chiffre d'affaires - Achats - Charges externes - Impôts et taxes - Charges de personnel
                        </div>
                        <p>L'EBE mesure la richesse générée par l'exploitation avant amortissements et provisions.</p>
                        <div class="alert alert-warning">
                            <strong>💡 Interprétation :</strong><br>
                            - EBE > 0 : L'entreprise génère de la richesse<br>
                            - EBE < 0 : L'exploitation est déficitaire
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 4. Capacité d'Autofinancement (CAF)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📊 Formule CAF :</strong><br>
                            CAF = EBE - Charges financières + Produits financiers
                        </div>
                        <p>La CAF représente les ressources internes générées par l'entreprise.</p>
                        <div class="alert alert-success">
                            <strong>💡 Interprétation :</strong><br>
                            - CAF > 0 : L'entreprise peut autofinancer ses investissements<br>
                            - CAF < 0 : Besoin de financement externe
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 5. Le levier opérationnel</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📊 Formule :</strong><br>
                            Levier opérationnel = Marge sur coût variable / Résultat
                        </div>
                        <p>Mesure la sensibilité du résultat aux variations du chiffre d'affaires.</p>
                        <div class="alert alert-info">
                            <strong>💡 Exemple :</strong> Levier = 4 → 1% d'augmentation du CA = 4% d'augmentation du résultat.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 6 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 6. Analyse en avenir aléatoire</div>
                    <div class="card-body">
                        <p>L'analyse en avenir aléatoire intègre la probabilité des différents scénarios.</p>
                        <div class="alert alert-primary">
                            <strong>📊 Espérance mathématique :</strong><br>
                            E(R) = Σ (Résultat_scénario × Probabilité_scénario)
                        </div>
                        <div class="alert alert-light">
                            <strong>📝 Exemple :</strong><br>
                            Optimiste (30%) : Résultat = 500 000<br>
                            Réaliste (50%) : Résultat = 300 000<br>
                            Pessimiste (20%) : Résultat = 100 000<br>
                            E(R) = 500000×0,3 + 300000×0,5 + 100000×0,2 = <strong>320 000 F</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 7 -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">📋 7. Cas pratique - Société Générale</div>
                    <div class="card-body">
                        <p>Accédez au module d'analyse complet :</p>
                        <a href="seuil_rentabilite.php" class="btn btn-primary">🔗 Voir l'analyse financière</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
