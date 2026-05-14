<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel de formation - Comptabilité Analytique";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel de formation - Comptabilité Analytique (CAE)</h5>
                <small>Guide complet - SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Définition et objectifs de la CAE</li>
                        <li>La hiérarchie des coûts</li>
                        <li>Charges directes et indirectes</li>
                        <li>Charges non incorporables et charges supplétives</li>
                        <li>Le calcul du CMUP (Coût Moyen Unitaire Pondéré)</li>
                        <li>Le passage du résultat CG au résultat CAE</li>
                        <li>Cas pratique Société Générale</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 1. Définition et objectifs de la CAE</div>
                    <div class="card-body">
                        <p>La <strong>Comptabilité Analytique (CAE)</strong> est un outil de gestion qui permet de :</p>
                        <ul>
                            <li>Calculer les coûts des produits, services ou projets</li>
                            <li>Analyser la rentabilité par centre de responsabilité</li>
                            <li>Aider à la prise de décision (fixation des prix, abandon de produit)</li>
                        </ul>
                        <div class="alert alert-success">
                            <strong>✅ Objectif :</strong> Déterminer le coût de revient pour piloter l'entreprise.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📊 2. La hiérarchie des coûts</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-primary text-white">Hiérarchie des coûts</div>
                                    <div class="card-body">
                                        <ul>
                                            <li><strong>Coût d'achat</strong> = Prix d'achat + Frais d'approvisionnement</li>
                                            <li><strong>Coût de production</strong> = Coût d'achat + Charges de production</li>
                                            <li><strong>Coût de revient</strong> = Coût de production + Frais de distribution</li>
                                            <li><strong>Résultat analytique</strong> = Prix de vente - Coût de revient</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-success text-white">Schéma des coûts</div>
                                    <div class="card-body">
                                        <pre class="bg-dark text-white p-2 rounded">
Charges directes ──┐
                  ├──► Coût de production
Charges indirectes─┘
                          │
                          ▼
                    + Frais de distribution
                          │
                          ▼
                    Coût de revient
                          │
                          ▼
                    Résultat analytique
                        </pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">💰 3. Charges directes et indirectes</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-primary">
                                    <strong>📌 CHARGES DIRECTES</strong>
                                    <ul class="mt-2 mb-0">
                                        <li>Achats de matières premières</li>
                                        <li>Salaires du personnel de production</li>
                                        <li>Énergie (machine spécifique)</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-secondary">
                                    <strong>📌 CHARGES INDIRECTES</strong>
                                    <ul class="mt-2 mb-0">
                                        <li>Loyer (administration)</li>
                                        <li>Électricité générale</li>
                                        <li>Salaires du personnel administratif</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">⚠️ 4. Charges non incorporables et charges supplétives</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">Charges non incorporables (à ajouter)</div>
                                    <div class="card-body">
                                        <ul>
                                            <li>Dotations excédentaires</li>
                                            <li>Provisions pour risques</li>
                                            <li>Impôts sur les résultats</li>
                                            <li>Charges exceptionnelles</li>
                                        </ul>
                                        <div class="alert alert-light">
                                            <strong>📝 Formule :</strong> Résultat CAE = Résultat CG + Charges non incorporables
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">Charges supplétives (à retrancher)</div>
                                    <div class="card-body">
                                        <ul>
                                            <li>Rémunération du dirigeant non payée</li>
                                            <li>Rémunération du capital (intérêts)</li>
                                            <li>Amortissements accélérés</li>
                                        </ul>
                                        <div class="alert alert-light">
                                            <strong>📝 Formule :</strong> Résultat CAE = Résultat CG - Charges supplétives
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🧮 5. Le calcul du CMUP</div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>📊 Formule CMUP :</strong><br>
                            CMUP = (Stock initial en valeur + Somme des entrées en valeur) / (Stock initial en quantité + Somme des entrées en quantité)
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Date</th><th>Opération</th><th>Quantité</th><th>Prix unitaire</th><th>Valeur totale</th><th>CMUP</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>01/01</td><td>Stock initial</td><td>100 kg</td><td>1 000 F</td><td>100 000 F</td><td>1 000 F</td></tr>
                                    <tr><td>10/01</td><td>Achat</td><td>200 kg</td><td>1 100 F</td><td>220 000 F</td><td>1 067 F</td></tr>
                                    <tr><td>15/02</td><td>Achat</td><td>150 kg</td><td>1 050 F</td><td>157 500 F</td><td>1 060 F</td></tr>
                                    <tr><td>20/03</td><td>Sortie</td><td>180 kg</td><td>1 060 F</td><td>190 800 F</td><td>-</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 6 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🔄 6. Passage du résultat CG au résultat CAE</div>
                    <div class="card-body">
                        <div class="alert alert-success text-center">
                            <h5>Résultat CAE = Résultat CG + Charges non incorporables - Charges supplétives</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="card bg-secondary text-white">
                                    <div class="card-body">Résultat CG<br><strong>2 550 000 F</strong></div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body">+ Charges non incorporables<br><strong>+ 750 000 F</strong></div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card bg-info text-white">
                                    <div class="card-body">- Charges supplétives<br><strong>- 500 000 F</strong></div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-success text-center mt-3">
                            <h4>Résultat CAE = 2 550 000 + 750 000 - 500 000 = <strong class="text-primary">2 800 000 F</strong></h4>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 7 -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">📋 7. Cas pratique - Société Générale</div>
                    <div class="card-body">
                        <p>Accédez au module d'analyse complet :</p>
                        <a href="analyse_cae.php" class="btn btn-primary">🔗 Voir l'analyse CAE complète</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
