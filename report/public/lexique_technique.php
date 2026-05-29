<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Lexique technique – Finance & Comptabilité (version intégrale)";
include 'inc_navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .term-card { transition: 0.2s; border-left: 5px solid #0d6efd; margin-bottom: 15px; }
        .term-card:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .term-title { font-weight: bold; font-size: 1rem; cursor: pointer; }
        .term-def { color: #2c3e50; font-size: 0.9rem; }
        .badge-cat { font-size: 0.7rem; margin-left: 10px; }
        .nav-pills .nav-link.active { background-color: #0d6efd; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-book"></i> Lexique technique – Finance & Comptabilité</h2>
                    <p>Plus de 250 termes essentiels – Formation continue – Conforme SYSCOHADA / IFRS</p>
                </div>
                <div class="card-body">

                    <!-- Filtres -->
                    <ul class="nav nav-pills mb-4 justify-content-center flex-wrap">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#general">📋 Comptabilité générale</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#analyse">📊 Analyse financière</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#marches">📈 Marchés & investissement</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#derives">🎲 Produits dérivés</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#gestion">⚙️ Gestion & contrôle</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#audit">🔍 Audit & conformité</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#struct">🏗️ Finance structurée</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#international">🌍 Finance internationale</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#fiscal">💰 Fiscalité & régularisations</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#syscohada">📐 Spécifique SYSCOHADA</a></li>
                    </ul>

                    <div class="tab-content">
                        <!-- ==================== 1. COMPTABILITÉ GÉNÉRALE ==================== -->
                        <div class="tab-pane fade show active" id="general">
                            <div class="row">
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Actif</div><div class="term-def">Ressources contrôlées par l'entreprise (biens, créances, trésorerie).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Passif</div><div class="term-def">Obligations envers les tiers (dettes, capitaux propres).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Bilan</div><div class="term-def">État patrimonial à un instant T (Actif = Passif).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Compte de résultat</div><div class="term-def">Activité sur une période (Produits - Charges = Résultat net).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Capitaux propres</div><div class="term-def">Ressources des actionnaires (capital, réserves, report à nouveau).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Amortissement</div><div class="term-def">Perte de valeur irréversible d'une immobilisation.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Provision</div><div class="term-def">Constatation d'une charge ou perte probable.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Flux de trésorerie (Cash-flow)</div><div class="term-def">Mouvements d'argent réels entrants/sortants.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Écritures d'inventaire</div><div class="term-def">Régularisations de fin d'exercice (amortissements, provisions).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">CCA / PCA</div><div class="term-def">Charges / Produits constatés d'avance (cut-off).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Valeur nominale</div><div class="term-def">Valeur faciale du titre à l'émission.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Valeur comptable</div><div class="term-def">Capitaux propres / nombre d'actions.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Valeur intrinsèque</div><div class="term-def">Valeur théorique basée sur les flux futurs actualisés.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Juste valeur (Fair value)</div><div class="term-def">Prix de marché entre parties consentantes.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Goodwill</div><div class="term-def">Écart d'acquisition (survaleur).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Actif immobilisé</div><div class="term-def">Biens destinés à rester durablement dans l'entreprise.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Actif circulant</div><div class="term-def">Biens destinés à être vendus ou consommés à court terme.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Dettes à court terme</div><div class="term-def">Obligations exigibles à moins d'un an.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Dettes à long terme</div><div class="term-def">Obligations exigibles à plus d'un an.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Résultat net</div><div class="term-def">Différence entre produits et charges (bénéfice ou perte).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Report à nouveau</div><div class="term-def">Bénéfice non distribué des exercices antérieurs.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Réserves</div><div class="term-def">Bénéfices mis de côté (légales, statutaires, facultatives).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Subventions d'investissement</div><div class="term-def">Aides publiques pour financer des immobilisations.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Provisions pour dépréciation</div><div class="term-def">Perte de valeur d'un actif (stock, créance).</div></div></div></div>
                            </div>
                        </div>

                        <!-- ==================== 2. ANALYSE FINANCIÈRE ==================== -->
                        <div class="tab-pane fade" id="analyse">
                            <div class="row">
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">EBE (Excédent Brut d'Exploitation)</div><div class="term-def">Rentabilité économique avant amortissements et politique financière.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">EBITDA</div><div class="term-def">Equivalent anglo-saxon de l'EBE (performance opérationnelle brute).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">BFR (Besoin en Fonds de Roulement)</div><div class="term-def">Stocks + Créances - Dettes fournisseurs (décalage de trésorerie).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">FRNG (Fonds de Roulement Net Global)</div><div class="term-def">Capitaux permanents - Actif immobilisé.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">CAF (Capacité d'Autofinancement)</div><div class="term-def">Ressources internes générées par l'activité.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Levier financier</div><div class="term-def">Utilisation de la dette pour augmenter la rentabilité des CP.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">ROE (Return on Equity)</div><div class="term-def">Résultat net / Capitaux propres.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">ROCE</div><div class="term-def">Rentabilité des capitaux employés.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">WACC (Coût moyen pondéré du capital)</div><div class="term-def">Taux minimal exigé par les apporteurs de capitaux.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">EVA (Economic Value Added)</div><div class="term-def">Valeur créée après rémunération de tous les apporteurs.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">MVA (Market Value Added)</div><div class="term-def">Différence valeur de marché et capital investi.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Gearing</div><div class="term-def">Dette nette / Capitaux propres (solidité financière).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Taux de marge brute</div><div class="term-def">Marge brute / CA.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Burn rate</div><div class="term-def">Vitesse de consommation de la trésorerie (startups).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">DCF (Discounted Cash Flow)</div><div class="term-def">Évaluation par actualisation des flux futurs.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Taux d'actualisation</div><div class="term-def">Taux reflétant le coût du capital et le risque.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Ratio d'endettement</div><div class="term-def">Dettes / Capitaux propres.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Ratio de liquidité générale</div><div class="term-def">Actif circulant / Dettes à court terme.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Ratio de liquidité réduite</div><div class="term-def">(Actif circulant - Stocks) / Dettes CT.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Ratio de liquidité immédiate</div><div class="term-def">Disponibilités / Dettes CT.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Rotation des stocks</div><div class="term-def">Coût des ventes / Stock moyen.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">DSO (Days Sales Outstanding)</div><div class="term-def">Délai moyen de paiement client.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">DPO (Days Payables Outstanding)</div><div class="term-def">Délai moyen de paiement fournisseur.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Covenant financier</div><div class="term-def">Clause contractuelle imposant des ratios à respecter.</div></div></div></div>
                            </div>
                        </div>

                        <!-- ==================== 3. MARCHÉS & INVESTISSEMENT ==================== -->
                        <div class="tab-pane fade" id="marches">
                            <div class="row">
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Dividende</div><div class="term-def">Part du bénéfice distribuée aux actionnaires.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Obligation</div><div class="term-def">Titre de créance avec intérêts (coupon).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Action</div><div class="term-def">Titre de propriété du capital.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Capitalisation boursière</div><div class="term-def">Nb actions × Cours.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">VAN (Valeur Actuelle Nette)</div><div class="term-def">Somme flux actualisés - Investissement.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">TRI (Taux de Rendement Interne)</div><div class="term-def">Taux qui annule la VAN.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Volatilité</div><div class="term-def">Ampleur des variations de prix.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Spread</div><div class="term-def">Écart bid / ask.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Covariance / Corrélation</div><div class="term-def">Relation statistique entre deux actifs.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Value investing</div><div class="term-def">Acheter des actions sous-évaluées.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Dilution</div><div class="term-def">Réduction de la part d'un actionnaire par émission de nouvelles actions.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Short selling (vente à découvert)</div><div class="term-def">Vendre un actif emprunté pour le racheter moins cher.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Asset allocation</div><div class="term-def">Répartition stratégique entre classes d'actifs.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Hedge fund</div><div class="term-def">Fonds spéculatif utilisant des stratégies agressives.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Mark-to-Market</div><div class="term-def">Valorisation à la valeur de marché.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Rating (notation)</div><div class="term-def">Évaluation de la qualité de crédit d'une entreprise.</div></div></div></div>
                            </div>
                        </div>

                        <!-- ==================== 4. PRODUITS DÉRIVÉS ==================== -->
                        <div class="tab-pane fade" id="derives">
                            <div class="row">
                                <div class="col-md-12"><div class="card bg-light mb-3"><div class="card-header">📌 Définition</div><div class="card-body">Instrument financier dont la valeur dépend d'un actif sous-jacent. Utilisé pour la couverture (hedging) ou la spéculation.</div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Option Call / Put</div><div class="term-def">Droit d'acheter (call) ou de vendre (put) à un prix fixé.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Future</div><div class="term-def">Contrat ferme d'achat/vente à date et prix fixés.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Swap</div><div class="term-def">Échange de flux financiers (taux fixe contre variable).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Forward</div><div class="term-def">Contrat de gré à gré non standardisé.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Option "In the money" (ITM)</div><div class="term-def">Option avec valeur intrinsèque positive.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Option "Out of the money" (OTM)</div><div class="term-def">Option sans valeur intrinsèque.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Volatilité implicite</div><div class="term-def">Anticipation des fluctuations futures du sous-jacent.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Contango</div><div class="term-def">Prix futur > prix au comptant.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Backwardation</div><div class="term-def">Prix futur < prix au comptant (pénurie immédiate).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Delta (option)</div><div class="term-def">Sensibilité du prix de l'option à la variation du sous-jacent.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">CDS (Credit Default Swap)</div><div class="term-def">Assurance contre le défaut d'un émetteur.</div></div></div></div>
                            </div>
                        </div>

                        <!-- ==================== 5. GESTION & CONTRÔLE ==================== -->
                        <div class="tab-pane fade" id="gestion">
                            <div class="row">
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Coût de revient</div><div class="term-def">Somme des charges pour produire un bien/service.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Seuil de rentabilité (point mort)</div><div class="term-def">CA à partir duquel bénéfice > 0.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Marge sur coût variable</div><div class="term-def">PV - Coûts variables.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Budget glissant</div><div class="term-def">Budget révisé périodiquement (ex: mensuel).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Cash-Pooling</div><div class="term-def">Centralisation de trésorerie au sein d'un groupe.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque de change</div><div class="term-def">Risque lié aux variations des devises.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">DSO (Délai moyen de paiement client)</div><div class="term-def">Temps moyen des règlements clients.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Affacturage (Factoring)</div><div class="term-def">Cession des créances clients à un factor.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Agios</div><div class="term-def">Intérêts de découvert bancaire.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Coûts fixes / variables</div><div class="term-def">Fixes : indépendants du volume ; variables : proportionnels.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Écart sur coûts</div><div class="term-def">Différence réel vs préétabli (budget).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Plan de trésorerie</div><div class="term-def">Prévision mensuelle des encaissements/décaissements.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Escompte commercial</div><div class="term-def">Avance de trésorerie sur facture client par la banque.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Levier opérationnel</div><div class="term-def">Sensibilité du résultat d'exploitation à la variation du CA.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Direct costing</div><div class="term-def">Imputation aux produits des seules charges variables directes.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Imputation rationnelle</div><div class="term-def">Incorporation des charges fixes basée sur une activité normale.</div></div></div></div>
                            </div>
                        </div>

                        <!-- ==================== 6. AUDIT & CONFORMITÉ ==================== -->
                        <div class="tab-pane fade" id="audit">
                            <div class="row">
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Audit</div><div class="term-def">Examen professionnel des comptes.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Lettrage</div><div class="term-def">Rapprochement débits/crédits d'un compte tiers.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Cut-off (indépendance des exercices)</div><div class="term-def">Rattachement à l'exercice correspondant.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Consolidation</div><div class="term-def">Regroupement des comptes d'un groupe.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Audit trail (piste d'audit)</div><div class="term-def">Historique complet des modifications.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Due Diligence</div><div class="term-def">Audit approfondi pré-acquisition.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Covenant bancaire</div><div class="term-def">Clauses restrictives imposées par le prêteur.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Back-testing</div><div class="term-def">Test d'un modèle sur données historiques.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Sondage d'audit</div><div class="term-def">Vérification d'un échantillon représentatif.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Séparation des tâches</div><div class="term-def">Principe anti-fraude (initiation, autorisation, enregistrement séparés).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Audit de conformité</div><div class="term-def">Vérification du respect des normes (SYSCOHADA).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Matérialité (seuil de signification)</div><div class="term-def">Montant à partir duquel une erreur influence la décision.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Commissaire aux comptes</div><div class="term-def">Auditeur externe certifiant les comptes.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Expert-comptable</div><div class="term-def">Professionnel tenant les comptes et conseillant l'entreprise.</div></div></div></div>
                            </div>
                        </div>

                        <!-- ==================== 7. FINANCE STRUCTURÉE ==================== -->
                        <div class="tab-pane fade" id="struct">
                            <div class="row">
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">LBO (Leveraged Buy-Out)</div><div class="term-def">Acquisition financée majoritairement par dette.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Dette senior</div><div class="term-def">Dette prioritaire en cas de liquidation.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Mezzanine</div><div class="term-def">Financement hybride (dette + capital).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Leasing (Crédit-bail)</div><div class="term-def">Location avec option d'achat.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Dette subordonnée</div><div class="term-def">Remboursée après les dettes senior (plus risquée).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Taux zéro-coupon</div><div class="term-def">Obligation sans coupon, émise avec décote.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">ABS (Asset Backed Securities)</div><div class="term-def">Titres adossés à des actifs financiers.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">VaR (Value at Risk)</div><div class="term-def">Perte maximale potentielle avec un niveau de confiance donné.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Stress testing</div><div class="term-def">Simulation de scénarios extrêmes pour tester la résilience.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque de liquidité</div><div class="term-def">Impossibilité de vendre un actif rapidement sans perte.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque de contrepartie</div><div class="term-def">Risque de défaut du partenaire contractuel.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Back-to-Back</div><div class="term-def">Opération adossée pour neutraliser le risque de prix.</div></div></div></div>
                            </div>
                        </div>

                        <!-- ==================== 8. FINANCE INTERNATIONALE & ESG ==================== -->
                        <div class="tab-pane fade" id="international">
                            <div class="row">
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Arbitrage de taux d'intérêt</div><div class="term-def">Emprunter à bas taux, investir à haut taux.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">PPA (Parité de Pouvoir d'Achat)</div><div class="term-def">Ajustement des taux de change à long terme.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Couverture de change (Hedging)</div><div class="term-def">Verrouillage d'un taux de change futur.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Transfer Pricing (Prix de transfert)</div><div class="term-def">Facturation interne entre entités d'un groupe.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">ESG (Environnemental, Social, Gouvernance)</div><div class="term-def">Critères extra-financiers d'évaluation.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Agence de notation (Rating)</div><div class="term-def">Évaluation de la capacité à rembourser la dette.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">IFRS (International Financial Reporting Standards)</div><div class="term-def">Normes comptables internationales.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Consolidation par intégration globale</div><div class="term-def">Addition de tous les postes des filiales.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Mise en équivalence</div><div class="term-def">Méthode pour sociétés sous influence notable.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Capitaux propres négatifs</div><div class="term-def">Pertes cumulées supérieures au capital social (faillite technique).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Shareholder value</div><div class="term-def">Priorité à la maximisation de la richesse des actionnaires.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Gouvernance d'entreprise</div><div class="term-def">Règles de direction et de contrôle de l'entreprise.</div></div></div></div>
                            </div>
                        </div>

                        <!-- ==================== 9. FISCALITÉ & RÉGULARISATIONS ==================== -->
                        <div class="tab-pane fade" id="fiscal">
                            <div class="row">
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Réintégration fiscale</div><div class="term-def">Ajout au résultat comptable de charges non déductibles.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Déduction fiscale</div><div class="term-def">Soustraction au résultat comptable de produits non imposables.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">TVA intracommunautaire</div><div class="term-def">Traitement spécifique des flux entre pays de l'UE.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Amortissement dérogatoire</div><div class="term-def">Avantage fiscal au-delà de l'amortissement économique.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">IS (Impôt sur les Sociétés)</div><div class="term-def">Impôt sur les bénéfices des entreprises.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">TVA déductible / collectée</div><div class="term-def">TVA sur achats (déductible) / TVA sur ventes (collectée).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">CVAE (Cotisation sur la Valeur Ajoutée des Entreprises)</div><div class="term-def">Taxe proportionnelle à la valeur ajoutée.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">CFE (Cotisation Foncière des Entreprises)</div><div class="term-def">Taxe locale sur les immobilisations.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Crédit d'impôt</div><div class="term-def">Somme déductible de l'impôt (ex: CIR).</div></div></div></div>
                            </div>
                        </div>

                        <!-- ==================== 10. SPÉCIFIQUE SYSCOHADA ==================== -->
                        <div class="tab-pane fade" id="syscohada">
                            <div class="row">
                                <div class="col-md-12"><div class="card bg-light mb-3"><div class="card-header">📌 SYSCOHADA (Système Comptable de l'OHADA)</div><div class="card-body">Norme comptable unique pour les 17 pays de l'OHADA (Afrique). Elle impose un plan comptable unifié et des états financiers normalisés.</div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Plan comptable UEMOA</div><div class="term-def">Nomenclature des comptes à 8 classes (1 à 8).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Classe 1</div><div class="term-def">Comptes de capitaux (capital, réserves).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Classe 2</div><div class="term-def">Immobilisations.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Classe 3</div><div class="term-def">Stocks et en-cours.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Classe 4</div><div class="term-def">Tiers (clients, fournisseurs).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Classe 5</div><div class="term-def">Trésorerie (banques, caisse).</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Classe 6</div><div class="term-def">Charges.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Classe 7</div><div class="term-def">Produits.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Classe 8</div><div class="term-def">Comptes spéciaux.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">État financier SYSCOHADA</div><div class="term-def">Bilan, compte de résultat, SIG, tableau de financement.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Compte de liaison</div><div class="term-def">Compte entre siège et succursales.</div></div></div></div>
                                <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Écart de conversion</div><div class="term-def">Ajustement lié aux variations de change.</div></div></div></div>
                            </div>
                        </div>
                    </div>

                    <!-- RÉCAPITULATIF DES 3 PILIERS -->
                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="card text-white bg-primary">
                                <div class="card-body text-center">
                                    <h4>📋 La Comptabilité</h4>
                                    <p>Le passé – Enregistre et certifie les flux (Historique).</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-success">
                                <div class="card-body text-center">
                                    <h4>⚙️ La Gestion</h4>
                                    <p>Le présent – Analyse les coûts et la performance (Pilotage).</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-warning">
                                <div class="card-body text-center">
                                    <h4>📈 La Finance</h4>
                                    <p>Le futur – Évalue les risques et crée la valeur (Stratégie).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle"></i> Lexique évolutif – Plus de 250 termes techniques couvrant la comptabilité, l'analyse financière, les marchés, les produits dérivés, la gestion, l'audit, la finance structurée, la finance internationale, la fiscalité et SYSCOHADA.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'inc_footer.php'; ?>
