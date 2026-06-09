<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Retraitements approfondis – Bilan, BFR, CAF, analyse financière";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';
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
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .retraitement-card { transition: 0.2s; border-left: 5px solid #0d6efd; margin-bottom: 20px; }
        .retraitement-card:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .formule { background: #f8f9fa; padding: 10px; border-radius: 8px; font-family: monospace; text-align: center; }
        .impact-positif { color: #28a745; font-weight: bold; }
        .impact-negatif { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-arrow-repeat"></i> Retraitements approfondis – Bilan, BFR, CAF, analyse financière</h2>
                    <p>Guide pédagogique complet : capitaux non appelés, amortissements, crédit-bail, provisions, capacité d'autofinancement</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : GUIDE THÉORIQUE ==================== -->
                    <h4 class="section-title"><i class="bi bi-book"></i> 1. Pourquoi retraiter les comptes ?</h4>
                    <div class="alert alert-info">
                        <strong>📌 Objectif :</strong> Transformer les documents comptables (conçus pour le fisc et les actionnaires) en une <strong>lecture économique réelle</strong> de l'entreprise.
                    </div>

                    <!-- ==================== SECTION 2 : RETRAITEMENTS DU BILAN ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-building"></i> 2. Retraitements du bilan (structure financière)</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card retraitement-card">
                                <div class="card-header bg-light fw-bold">💰 Capital souscrit non appelé</div>
                                <div class="card-body">
                                    <p><strong>Concept :</strong> Promesse d'apport d'argent des actionnaires non encore réclamée.</p>
                                    <div class="alert alert-secondary">
                                        <strong>Action :</strong><br>
                                        - Soustraire de l'actif<br>
                                        - Soustraire des capitaux propres au passif
                                    </div>
                                    <p><strong>Pourquoi ?</strong> Pour ne pas surévaluer les fonds propres réellement disponibles.</p>
                                    <div class="formule">Capitaux Propres retraités = CP comptables - Capital non appelé</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card retraitement-card">
                                <div class="card-header bg-light fw-bold">📉 Amortissements et dépréciations</div>
                                <div class="card-body">
                                    <p><strong>Concept :</strong> La comptabilité estime que les machines perdent de la valeur chaque année.</p>
                                    <div class="alert alert-secondary">
                                        <strong>Action :</strong><br>
                                        - Réintégrer les amortissements dans les Capitaux Propres<br>
                                        - Ajouter à l'actif brut
                                    </div>
                                    <p><strong>Pourquoi ?</strong> Pour calculer la "Valeur Économique" réelle de l'entreprise.</p>
                                    <div class="formule">Actif économique = Actif net comptable + Amortissements cumulés</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card retraitement-card">
                                <div class="card-header bg-light fw-bold">🔧 Gros entretien (Charge vs Investissement)</div>
                                <div class="card-body">
                                    <p><strong>Problème :</strong> Une grosse réparation peut faire plonger le bénéfice une année.</p>
                                    <div class="alert alert-secondary">
                                        <strong>Action :</strong><br>
                                        - Transformer la charge exceptionnelle en immobilisation<br>
                                        - Étaler le coût sur plusieurs années
                                    </div>
                                    <p><strong>Pourquoi ?</strong> Pour lisser la performance sur le long terme.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 3 : RETRAITEMENTS COMPTE DE RÉSULTAT ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-bar-chart"></i> 3. Retraitements du compte de résultat (performance)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card retraitement-card">
                                <div class="card-header bg-light fw-bold">⚠️ Provisions pour risques et charges</div>
                                <div class="card-body">
                                    <p><strong>Concept :</strong> L'entreprise met de l'argent de côté pour une dépense probable.</p>
                                    <div class="alert alert-secondary">
                                        <strong>Action :</strong><br>
                                        - Réintégrer dans les capitaux propres si le risque est peu probable
                                    </div>
                                    <p><strong>Pourquoi ?</strong> Pour vérifier si l'entreprise ne "cache" pas des profits.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card retraitement-card">
                                <div class="card-header bg-light fw-bold">📊 Crédit-bail (Leasing) – Retraitement complet</div>
                                <div class="card-body">
                                    <p><strong>Concept :</strong> Économiquement, louer revient à emprunter pour acheter.</p>
                                    <div class="alert alert-secondary">
                                        <strong>Action :</strong><br>
                                        - Réintégrer la valeur de la machine à l'actif<br>
                                        - Créer une dette financière fictive au passif<br>
                                        - Calculer les intérêts théoriques
                                    </div>
                                    <p><strong>Pourquoi ?</strong> Pour comparer entreprises qui achètent vs qui louent sur le même pied.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 4 : CAS PRATIQUE TECHINDUSTRIE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 4. Cas pratique – Société "TechIndustrie"</h4>
                    <div class="alert alert-secondary">
                        <strong>📊 Données comptables (avant retraitements) :</strong><br>
                        Actif Circulant (Stocks + Clients) : 500 000 € | Dettes Fournisseurs : 300 000 €<br>
                        Dettes Financières (Emprunts) : 400 000 € | Capitaux Propres : 200 000 €<br>
                        Amortissements cumulés : 100 000 € | Crédit-bail : valeur actuelle 150 000 €
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr><th>Indicateur</th><th>Avant retraitement</th><th>Après retraitement</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>BFR (Besoin en Fonds de Roulement)</td>
                                            <td class="text-end">200 000 €</td>
                                            <td class="text-end">200 000 €</td>
                                        </tr>
                                        <tr><td>Ratio de liquidité générale</td>
                                            <td class="text-end">1,66</td>
                                            <td class="text-end">1,25</td>
                                        </tr>
                                        <tr><td>Taux d'endettement</td>
                                            <td class="text-end">2,0</td>
                                            <td class="text-end">2,16</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>📌 Impact des retraitements :</strong><br>
                                - Ajout crédit-bail à l'actif : <span class="text-success">+150 000 €</span><br>
                                - Création dette financière : <span class="text-danger">+150 000 €</span><br>
                                - Réintégration amortissements aux CP : <span class="text-success">+100 000 €</span>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 5 : BFR ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-arrow-left-right"></i> 5. Le Besoin en Fonds de Roulement (BFR)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="formule mb-3">
                                BFR = Stocks + Créances Clients - Dettes Fournisseurs
                            </div>
                            <div class="formule">
                                BFR (en jours de CA) = (BFR / Chiffre d'affaires HT) × 365
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <strong>⚠️ Paradoxe de la croissance :</strong> Plus vous vendez, plus vous avez besoin de fonds pour financer votre BFR avant d'encaisser.
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 6 : CAF ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-piggy-bank"></i> 6. Capacité d'Autofinancement (CAF)</h4>
                    <div class="formule mb-3">
                        CAF comptable = Résultat Net + Dotations aux amortissements/provisions - Reprises
                    </div>
                    <div class="formule">
                        CAF économique = CAF comptable + Part capital du loyer de crédit-bail + Provisions non décaissables
                    </div>
                    <div class="alert alert-info mt-3">
                        <strong>📌 Ratio de capacité de remboursement :</strong><br>
                        Dette Totale (Bancaire + Crédit-Bail) / CAF Économique<br>
                        <small>Interprétation : < 4 ans = bonne santé, > 5 ans = sous pression</small>
                    </div>

                    <!-- ==================== SECTION 7 : SIMULATEUR ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-sliders"></i> 6. Simulateur interactif – Impact des retraitements</h4>
                    <div class="card bg-light p-3">
                        <form method="post">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Actif circulant (€)</label>
                                    <input type="number" name="actif_circ" class="form-control" value="500000" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Dettes fournisseurs (€)</label>
                                    <input type="number" name="dettes_fourn" class="form-control" value="300000" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Capitaux propres (€)</label>
                                    <input type="number" name="cp" class="form-control" value="200000" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Crédit-bail (valeur actuelle €)</label>
                                    <input type="number" name="credit_bail" class="form-control" value="150000" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Amortissements cumulés (€)</label>
                                    <input type="number" name="amortissements" class="form-control" value="100000" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Capital non appelé (€)</label>
                                    <input type="number" name="capital_non_appele" class="form-control" value="0" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="simuler" class="btn btn-primary">Lancer la simulation</button>
                                </div>
                            </div>
                        </form>

                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simuler'])) {
                            $actif_circ = (float)$_POST['actif_circ'];
                            $dettes_fourn = (float)$_POST['dettes_fourn'];
                            $cp = (float)$_POST['cp'];
                            $credit_bail = (float)$_POST['credit_bail'];
                            $amortissements = (float)$_POST['amortissements'];
                            $capital_non_appele = (float)$_POST['capital_non_appele'];
                            
                            $bfr = $actif_circ - $dettes_fourn;
                            $liquidite_avant = $actif_circ / $dettes_fourn;
                            $endettement_avant = $actif_circ / $cp;
                            
                            $actif_circ_apres = $actif_circ + $credit_bail;
                            $cp_apres = $cp + $amortissements - $capital_non_appele;
                            $dettes_totales_apres = $dettes_fourn + $credit_bail;
                            $liquidite_apres = $actif_circ_apres / $dettes_totales_apres;
                            $endettement_apres = ($dettes_fourn + $credit_bail) / $cp_apres;
                            
                            echo <<<HTML
                            <div class="alert alert-success mt-4">
                                <strong>📊 Résultats de la simulation :</strong>
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr><th>Indicateur</th><th>Avant retraitement</th><th>Après retraitement</th><th>Variation</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>BFR (k€)</td>
                                                <td class="text-end">" . number_format($bfr / 1000, 0, ',', ' ') . " k€</td>
                                                <td class="text-end">" . number_format($bfr / 1000, 0, ',', ' ') . " k€</td>
                                                <td class="text-center">-</td>
                                            </tr>
                                            <tr><td>Ratio de liquidité</td>
                                                <td class="text-end">" . number_format($liquidite_avant, 2) . "</td>
                                                <td class="text-end">" . number_format($liquidite_apres, 2) . "</td>
                                                <td class="text-center " . ($liquidite_apres < $liquidite_avant ? 'text-danger' : 'text-success') . ">" . number_format(($liquidite_apres - $liquidite_avant) * 100, 1) . "%</td>
                                            </tr>
                                            <tr><td>Taux d'endettement</td>
                                                <td class="text-end">" . number_format($endettement_avant, 2) . "</td>
                                                <td class="text-end">" . number_format($endettement_apres, 2) . "</td>
                                                <td class="text-center " . ($endettement_apres > $endettement_avant ? 'text-danger' : 'text-success') . "">" . number_format(($endettement_apres - $endettement_avant) * 100, 1) . "%</td>
                                            </tr>
                                            <tr><td>Capitaux propres retraités (k€)</td>
                                                <td class="text-end">" . number_format($cp / 1000, 0, ',', ' ') . " k€</td>
                                                <td class="text-end">" . number_format($cp_apres / 1000, 0, ',', ' ') . " k€</td>
                                                <td class="text-center text-success">+" . number_format(($cp_apres - $cp) / 1000, 0, ',', ' ') . " k€</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="mt-3"><strong>💡 Interprétation :</strong> " . ($liquidite_apres < $liquidite_avant ? "Le crédit-bail masque une fragilité réelle de liquidité." : "Structure financière améliorée.") . "</p>
                            </div>
HTML;
                        }
                        ?>
                    </div>

                    <!-- ==================== SECTION 8 : SYNTHÈSE ==================== -->
                    <div class="alert alert-success mt-4">
                        <i class="bi bi-check-circle-fill"></i> <strong>Synthèse pour l'analyste financier :</strong><br>
                        La CAF mesure la génération de cash. Le BFR mesure l'immobilisation de ce cash.<br>
                        <strong>Free Cash Flow = CAF - Variation du BFR - Investissements</strong><br>
                        Si ce flux est positif et croissant, l'entreprise est structurellement solide.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
