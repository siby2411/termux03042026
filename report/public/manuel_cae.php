<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Manuel de comptabilité analytique (CAE)";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-journal-bookmark-fill"></i> Manuel de formation : Comptabilité analytique (CAE)</h5>
                    <small>Conforme SYSCOHADA révisé / IFRS</small>
                </div>
                <div class="card-body">

                    <!-- SOMMAIRE -->
                    <div class="alert alert-info">
                        <strong>📚 SOMMAIRE</strong>
                        <ol class="mb-0 mt-2">
                            <li>Objectifs de la comptabilité analytique</li>
                            <li>La notion de coût complet</li>
                            <li>Les coûts variables et la marge sur coût variable</li>
                            <li>Les coûts directs et indirects</li>
                            <li>Les centres d’analyse (sections homogènes)</li>
                            <li>La méthode ABC (Activity Based Costing)</li>
                            <li>Cas pratique : calcul du coût de revient</li>
                            <li>Tableau de répartition des charges indirectes</li>
                            <li>Analyse d’écarts (coût préétabli / coût réel)</li>
                        </ol>
                    </div>

                    <!-- 1. Objectifs -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">1. Objectifs de la comptabilité analytique</div>
                        <div class="card-body">
                            <p>La comptabilité analytique a pour but de :</p>
                            <ul>
                                <li>Calculer les coûts des produits ou services</li>
                                <li>Analyser les résultats par produit, par marché, par zone</li>
                                <li>Aider à la prise de décision (abandon de produit, sous-traitance, prix de vente)</li>
                                <li>Contrôler la performance des centres de responsabilité</li>
                            </ul>
                        </div>
                    </div>

                    <!-- 2. Coût complet -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">2. La notion de coût complet</div>
                        <div class="card-body">
                            <p>Le coût complet inclut <strong>toutes les charges</strong> (directes et indirectes) supportées par un produit.</p>
                            <div class="alert alert-primary">
                                <strong>Formule :</strong> Coût complet = Coût d’achat + Coût de production + Coût de distribution + Coût administratif
                            </div>
                            <p>Méthode des <strong>centres d’analyse</strong> : on répartit les charges indirectes en fonction d’unités d’œuvre.</p>
                        </div>
                    </div>

                    <!-- 3. Coûts variables -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">3. Coûts variables et marge sur coût variable</div>
                        <div class="card-body">
                            <p>Seules les charges variables sont imputées. La marge sur coût variable (MSCV) est un indicateur clé.</p>
                            <div class="alert alert-success">
                                <strong>MSCV = Chiffre d’affaires – Coûts variables</strong><br>
                                Taux de MSCV = MSCV / CA<br>
                                Seuil de rentabilité = Charges fixes / Taux de MSCV
                            </div>
                        </div>
                    </div>

                    <!-- 4. Coûts directs et indirects -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">4. Coûts directs et indirects</div>
                        <div class="card-body">
                            <ul>
                                <li><strong>Coût direct</strong> : affecté sans ambiguïté à un produit (ex. matières premières, main-d’œuvre directe).</li>
                                <li><strong>Coût indirect</strong> : nécessite un clé de répartition (ex. loyer, énergie, frais de direction).</li>
                            </ul>
                        </div>
                    </div>

                    <!-- 5. Centres d'analyse -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">5. Centres d’analyse (sections homogènes)</div>
                        <div class="card-body">
                            <p>Les centres d’analyse regroupent des charges indirectes de nature homogène. Exemples :</p>
                            <ul>
                                <li>Centre « Approvisionnement » : unité d’œuvre = kg de matière achetée</li>
                                <li>Centre « Production » : unité d’œuvre = heure machine</li>
                                <li>Centre « Distribution » : unité d’œuvre = % du CA</li>
                            </ul>
                            <pre class="bg-dark text-white p-2 rounded">
Schéma de répartition :
Charges indirectes totales
    ↓ répartition primaire
Centres auxiliaires → Centres principaux
    ↓ répartition secondaire
Unité d’œuvre → Coût unitaire → Produits
                            </pre>
                        </div>
                    </div>

                    <!-- 6. Méthode ABC -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">6. Méthode ABC (Activity Based Costing)</div>
                        <div class="card-body">
                            <p>Alternative aux centres d’analyse, la méthode ABC identifie les activités et les inducteurs de coûts.</p>
                            <p>Étapes :</p>
                            <ol>
                                <li>Identifier les activités (passer commande, régler une facture, usiner…)</li>
                                <li>Calculer le coût de chaque activité</li>
                                <li>Déterminer les inducteurs (nombre de commandes, nombre de factures, heures de set-up…)</li>
                                <li>Imputer le coût des activités aux produits</li>
                            </ol>
                            <div class="alert alert-warning">
                                <strong>Avantage</strong> : meilleure traçabilité des coûts indirects, surtout quand la diversité des produits est forte.
                            </div>
                        </div>
                    </div>

                    <!-- 7. Cas pratique -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">7. Cas pratique : calcul du coût de revient</div>
                        <div class="card-body">
                            <p><strong>Données :</strong></p>
                            <ul>
                                <li>Produit X : 1000 unités fabriquées, 900 vendues</li>
                                <li>Coût d’achat matières premières : 15 000 €</li>
                                <li>Main-d’œuvre directe : 8 000 €</li>
                                <li>Charges indirectes de production : 12 000 € (réparties sur la base des heures machine, 2000 h au total, produit X utilise 800 h)</li>
                                <li>Frais de distribution : 5 000 € (répartis sur la base du nombre d’unités vendues)</li>
                            </ul>
                            <div class="alert alert-success">
                                Coût de production total = 15 000 + 8 000 + (12 000 × 800/2000) = 15 000 + 8 000 + 4 800 = 27 800 €<br>
                                Coût de revient unitaire = (27 800 / 900) + (5 000 / 900) = 30,89 + 5,56 = 36,45 €/u
                            </div>
                        </div>
                    </div>

                    <!-- 8. Tableau de répartition -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">8. Tableau de répartition des charges indirectes</div>
                        <div class="card-body">
                            <pre class="bg-dark text-white p-2 rounded">
┌─────────────────┬──────────┬──────────┬──────────┬──────────┐
│ Centres         │ Admin    │ Atelier A│ Atelier B│ Finition │
├─────────────────┼──────────┼──────────┼──────────┼──────────┤
│ Charges primaires│ 10 000   │ 15 000   │ 20 000   │ 5 000    │
│ Répartition     │ -10 000  │ +6 000   │ +4 000   │ 0        │
│ Total secondaire│ 0        │ 21 000   │ 24 000   │ 5 000    │
│ Unité d’œuvre   │ -        │ heure MOD│ heure mach│ pièce finie│
│ Nombre d’UO     │ -        │ 700      │ 800      │ 500      │
│ Coût de l’UO    │ -        │ 30 €     │ 30 €     │ 10 €     │
└─────────────────┴──────────┴──────────┴──────────┴──────────┘
                            </pre>
                        </div>
                    </div>

                    <!-- 9. Analyse d'écarts -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">9. Analyse des écarts (coût préétabli / coût réel)</div>
                        <div class="card-body">
                            <p>L’écart global = Coût réel – Coût préétabli. On le décompose en :</p>
                            <ul>
                                <li><strong>Écart sur volume</strong> (quantité réelle vs quantité prévue)</li>
                                <li><strong>Écart sur prix / coût</strong> (prix réel vs prix standard)</li>
                                <li><strong>Écart sur rendement</strong> (consommation réelle vs consommation standard)</li>
                            </ul>
                            <div class="alert alert-primary">
                                Exemple : Matières premières – Écart = (QR × PR) – (QS × PS)
                            </div>
                        </div>
                    </div>

                    <!-- Accès aux modules -->
                    <div class="alert alert-info mt-3">
                        <strong>🌐 ACCÈS AUX MODULES COMPTABILITÉ ANALYTIQUE :</strong><br>
                        <a href="couts_complets.php" class="btn btn-sm btn-primary">📊 Coûts complets</a>
                        <a href="couts_variables.php" class="btn btn-sm btn-primary">📈 Coûts variables</a>
                        <a href="couts_abc.php" class="btn btn-sm btn-primary">📐 Méthode ABC</a>
                        <a href="analyse_ecarts.php" class="btn btn-sm btn-primary">📉 Analyse des écarts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
