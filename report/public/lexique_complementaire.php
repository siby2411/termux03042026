<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Lexique complémentaire – Finance & Comptabilité (termes avancés)";
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
        .term-title { font-weight: bold; font-size: 1rem; }
        .term-def { color: #2c3e50; font-size: 0.9rem; }
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-book"></i> Lexique complémentaire – Finance & Comptabilité</h2>
                    <p>Termes avancés – Produits dérivés, finance de marché, ingénierie financière, gestion des risques, valorisation</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Ce lexique complète le précédent avec des termes plus techniques. Utilisez les sections pour naviguer.
                    </div>

                    <!-- ==================== SECTION 1 : PRODUITS DÉRIVÉS & STRUCTURÉS ==================== -->
                    <h4 class="section-title"><i class="bi bi-graph-up"></i> Produits dérivés & structurés</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Swap de taux d'intérêt</div><div class="term-def">Échange entre taux fixe et taux variable pour se couvrir.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Swap de devises (Cross Currency Swap)</div><div class="term-def">Échange de capital et d'intérêts dans deux devises différentes.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Option asiatique</div><div class="term-def">Le prix d'exercice est basé sur la moyenne des cours.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Option barrière</div><div class="term-def">Option activée ou désactivée si le sous-jacent atteint un seuil.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Option digitale (binary option)</div><div class="term-def">Paie un montant fixe si le sous-jacent dépasse un seuil.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Future sur indice</div><div class="term-def">Contrat sur la valeur future d'un indice boursier.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Warrant</div><div class="term-def">Option émise par une institution financière.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Certificat</div><div class="term-def">Produit dérivé reproduisant la performance d'un actif.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Turbo warrant</div><div class="term-def">Produit à effet de levier élevé avec barrière désactivante.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 2 : RISQUES FINANCIERS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-shield-exclamation"></i> Gestion des risques financiers</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque de marché</div><div class="term-def">Risque de perte lié aux fluctuations des prix (actions, taux, change).</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque de crédit</div><div class="term-def">Risque de défaut d'une contrepartie.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque opérationnel</div><div class="term-def">Risque de perte dû à des défaillances internes.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque de liquidité</div><div class="term-def">Impossibilité de vendre un actif rapidement.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque de change</div><div class="term-def">Fluctuation des devises.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque de taux</div><div class="term-def">Variation des taux d'intérêt.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Risque systémique</div><div class="term-def">Effondrement de tout un système financier.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">CVaR (Conditional Value at Risk)</div><div class="term-def">Perte moyenne au-delà du seuil VaR.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Stress test</div><div class="term-def">Simulation de scénarios extrêmes.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 3 : INDICATEURS AVANCÉS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-speedometer2"></i> Indicateurs financiers avancés</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">CROIC (Cash Return on Invested Capital)</div><div class="term-def">Flux de trésorerie / Capital investi.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">ROIC (Return on Invested Capital)</div><div class="term-def">Résultat opérationnel net après impôt / Capital investi.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">RONA (Return on Net Assets)</div><div class="term-def">Résultat / Actif net.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">CFROI (Cash Flow Return on Investment)</div><div class="term-def">Rentabilité basée sur les flux de trésorerie.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">NOPAT (Net Operating Profit After Tax)</div><div class="term-def">Résultat opérationnel net d'impôt.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">EBIT (Earnings Before Interest and Taxes)</div><div class="term-def">Résultat avant intérêts et impôts.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">EBITDAR</div><div class="term-def">EBIT + Loyers (pour comparer des entreprises aux politiques d'immobilisation différentes).</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Free Cash Flow to Firm (FCFF)</div><div class="term-def">Flux disponibles pour tous les apporteurs de capitaux.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Free Cash Flow to Equity (FCFE)</div><div class="term-def">Flux disponibles pour les actionnaires.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 4 : VALORISATION ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-coin"></i> Valorisation d'entreprise</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">PER (Price Earnings Ratio)</div><div class="term-def">Cours / Bénéfice par action.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">EV/EBITDA</div><div class="term-def">Valeur d'entreprise / EBITDA.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">EV/CA</div><div class="term-def">Valeur d'entreprise / Chiffre d'affaires.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Price/Book ratio (P/B)</div><div class="term-def">Cours / Valeur comptable par action.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Dividend Yield</div><div class="term-def">Dividende / Cours de l'action.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">ANR (Actif Net Réel)</div><div class="term-def">Valeur patrimoniale après réévaluation.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">ANCC (Actif Net Comptable Corrigé)</div><div class="term-def">ANR + plus-values latentes – moins-values latentes.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Valeur de rendement</div><div class="term-def">Capacité bénéficiaire / Taux de capitalisation.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Méthode des praticiens</div><div class="term-def">(ANCC + Valeur de rendement) / 2.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 5 : FINANCE COMPORTEMENTALE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-people"></i> Finance comportementale</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Biais de confirmation</div><div class="term-def">Tendance à ne chercher que les informations confirmant ses croyances.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Aversion aux pertes</div><div class="term-def">La perte est plus douloureuse que le gain équivalent.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Excès de confiance</div><div class="term-def">Surestimation de ses propres capacités.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Effet de disposition</div><div class="term-def">Vendre trop tôt les gagnants et conserver trop longtemps les perdants.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Mental accounting</div><div class="term-def">Traiter l'argent différemment selon son origine.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Biais d'ancrage</div><div class="term-def">Se fier trop à la première information reçue.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 6 : ESG & FINANCE DURABLE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-tree"></i> ESG & Finance durable</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Obligation verte (Green bond)</div><div class="term-def">Obligation finançant des projets environnementaux.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Obligation sociale (Social bond)</div><div class="term-def">Finance des projets à impact social positif.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">ISR (Investissement Socialement Responsable)</div><div class="term-def">Investissement intégrant des critères ESG.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Impact investing</div><div class="term-def">Investissement visant un impact social/environnemental mesurable.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Taxonomie européenne</div><div class="term-def">Classification des activités économiques durables.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">CSR (Responsabilité Sociétale des Entreprises)</div><div class="term-def">Intégration volontaire des préoccupations sociales et environnementales.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 7 : CRYPTOMONNAIES & ACTIFS NUMÉRIQUES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-currency-bitcoin"></i> Cryptomonnaies & actifs numériques</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Blockchain</div><div class="term-def">Registre distribué et sécurisé.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Bitcoin (BTC)</div><div class="term-def">Première cryptomonnaie décentralisée.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Ethereum (ETH)</div><div class="term-def">Plateforme de contrats intelligents.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Stablecoin</div><div class="term-def">Cryptomonnaie indexée sur une monnaie fiduciaire (USDT, USDC).</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">NFT (Non-Fungible Token)</div><div class="term-def">Actif numérique unique et non interchangeable.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">DeFi (Decentralized Finance)</div><div class="term-def">Services financiers sans intermédiaires centralisés.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 8 : FUSIONS & ACQUISITIONS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-arrow-left-right"></i> Fusions & acquisitions</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Fusion absorption</div><div class="term-def">Une société absorbante reprend une absorbée.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Fusion par création d'une nouvelle société</div><div class="term-def">Deux sociétés disparaissent pour en créer une nouvelle.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Prime de fusion</div><div class="term-def">Différence entre la valeur d'apport et la valeur comptable.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Goodwill (écart d'acquisition)</div><div class="term-def">Survaleur payée au-delà de l'ANCC.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Badwill (écart d'acquisition négatif)</div><div class="term-def">Achat en dessous de la valeur patrimoniale.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Prime de contrôle</div><div class="term-def">Surcoût pour acquérir une participation majoritaire.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Décote de minorité</div><div class="term-def">Moins-value pour une participation non contrôlante.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">LBO (Leveraged Buy-Out)</div><div class="term-def">Acquisition financée par endettement.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">MBO (Management Buy-Out)</div><div class="term-def">Rachat par les dirigeants.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 9 : NORMES COMPTABLES INTERNATIONALES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-globe"></i> Normes comptables internationales (IFRS / IAS)</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">IFRS 9</div><div class="term-def">Instruments financiers (classement, évaluation, dépréciation).</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">IFRS 15</div><div class="term-def">Produits des activités ordinaires (contrats avec les clients).</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">IFRS 16</div><div class="term-def">Contrats de location (comptabilisation au bilan).</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">IAS 36</div><div class="term-def">Dépréciation des actifs.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">IAS 38</div><div class="term-def">Immobilisations incorporelles.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">IFRS 7</div><div class="term-def">Instruments financiers : informations à fournir.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 10 : TECHNIQUES QUANTITATIVES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> Techniques quantitatives</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Modèle Black-Scholes</div><div class="term-def">Modèle mathématique d'évaluation des options.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Loi normale</div><div class="term-def">Distribution symétrique (loi de Gauss).</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Loi log-normale</div><div class="term-def">Distribution des prix d'actifs (asymétrique).</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Monte Carlo</div><div class="term-def">Simulation de multiples scénarios aléatoires.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Régression linéaire</div><div class="term-def">Modélisation de la relation entre variables.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Beta (β) du MEDAF</div><div class="term-def">Sensibilité d'un actif au marché.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Alpha</div><div class="term-def">Rendement excédentaire par rapport au marché.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Sharpe ratio</div><div class="term-def">Rendement ajusté du risque.</div></div></div></div>
                        <div class="col-md-4"><div class="card term-card"><div class="card-body"><div class="term-title">Sortino ratio</div><div class="term-def">Sharpe ratio tenant compte uniquement du risque baissier.</div></div></div></div>
                    </div>

                    <div class="alert alert-secondary mt-4">
                        <i class="bi bi-bookmark-check"></i> Ce lexique complémentaire couvre plus de 120 termes avancés. Utilisez-le en complément du lexique principal.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
