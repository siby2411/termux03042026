<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel d'Ingénierie Financière - Valorisation d'Entreprise";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel d'Ingénierie Financière</h5>
                <small>Valorisation d'entreprise - Méthodes PER, ANC, ANCC - Fusions et Acquisitions</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>La méthode du PER (Price Earning Ratio)</li>
                        <li>L'Actif Net Comptable (ANC)</li>
                        <li>L'Actif Net Comptable Corrigé (ANCC)</li>
                        <li>La Valeur Substantielle Brute (VSB)</li>
                        <li>Les Capitaux Permanents Nécessaires à l'Exploitation (CPNE)</li>
                        <li>La Parité de Fusion</li>
                        <li>L'effet de dilution et la prime de fusion</li>
                        <li>Cas pratique : Fusion des sociétés Alpha et Beta</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 - Méthode du PER -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📈 1. La méthode du PER (Price Earning Ratio)</div>
                    <div class="card-body">
                        <p>Le <strong>PER</strong> mesure combien de fois le bénéfice annuel un investisseur est prêt à payer pour acquérir une action.</p>
                        <div class="alert alert-primary">
                            <strong>📐 Formule :</strong><br>
                            <code>PER = Cours de l'action / Bénéfice net par action</code>
                        </div>
                        <h6>Cas pratique :</h6>
                        <p>Une entreprise réalise un bénéfice de 10 € par action. Son cours de bourse est de 150 €.</p>
                        <div class="alert alert-success">
                            <strong>✅ Calcul :</strong> PER = 150 / 10 = <strong>15</strong><br>
                            Les investisseurs paient 15 ans de bénéfices.
                        </div>
                        <div class="alert alert-warning">
                            <strong>💡 Pour les entreprises non cotées :</strong><br>
                            On utilise le "PER sectoriel" (moyenne des PER des entreprises cotées du même secteur).
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 - Actif Net Comptable -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🏦 2. L'Actif Net Comptable (ANC)</div>
                    <div class="card-body">
                        <p>C'est la valeur de l'entreprise vue sous l'angle du patrimoine (ce qu'il resterait si l'on vendait tout et payait les dettes).</p>
                        <div class="alert alert-primary">
                            <strong>📐 Formule :</strong><br>
                            <code>ANC = Capitaux propres + Provisions réglementées - Actifs fictifs</code>
                        </div>
                        <h6>Cas pratique :</h6>
                        <ul>
                            <li>Capitaux propres : 500 000 €</li>
                            <li>Provisions réglementées : 50 000 €</li>
                            <li>Frais de constitution (actifs fictifs) : 10 000 €</li>
                        </ul>
                        <div class="alert alert-success">
                            <strong>✅ Calcul :</strong> ANC = 500 000 + 50 000 - 10 000 = <strong>540 000 €</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 - Actif Net Comptable Corrigé -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📊 3. L'Actif Net Comptable Corrigé (ANCC)</div>
                    <div class="card-body">
                        <p>La comptabilité est historique. L'ANCC ajuste ces valeurs à la réalité du marché.</p>
                        <div class="alert alert-primary">
                            <strong>📐 Formule :</strong><br>
                            <code>ANCC = ANC + Plus-values latentes - Moins-values latentes - Impôts différés</code>
                        </div>
                        
                        <h6>Tableau comparatif : ANC vs ANCC</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Caractéristique</th><th>ANC</th><th>ANCC</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Base</td><td>Valeurs inscrites au bilan (historiques)</td><td>Valeurs réelles de marché (valeurs vénales)</td></tr>
                                    <tr><td>Vision</td><td>Statique et rétrospective</td><td>Dynamique et actuelle</td></tr>
                                    <tr><td>Fiabilité</td><td>Très précise, basée sur les factures</td><td>Subjective (nécessite expertises)</td></tr>
                                    <tr><td>Utilisation</td><td>Base de départ pour tout calcul</td><td>Prix réel de cession ou fusion</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h6 class="mt-3">Cas pratique détaillé :</h6>
                        <ul>
                            <li>Capitaux Propres : 1 000 000 €</li>
                            <li>Immobilisations corporelles (valeur comptable) : 800 000 € (Valeur de marché : 1 200 000 €)</li>
                            <li>Frais de constitution : 20 000 €</li>
                            <li>Stock (valeur comptable) : 100 000 € (Valeur réelle : 80 000 €)</li>
                        </ul>
                        <div class="alert alert-success">
                            <strong>✅ Calcul de l'ANC :</strong> 1 000 000 - 20 000 = <strong>980 000 €</strong><br>
                            <strong>✅ Calcul de l'ANCC :</strong> 980 000 + 400 000 (plus-value) - 20 000 (moins-value) = <strong>1 360 000 €</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 - Parité de Fusion -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🔄 4. La Parité de Fusion</div>
                    <div class="card-body">
                        <p>La parité de fusion détermine combien d'actions de la société absorbante doivent être créées pour rémunérer les actionnaires de la société absorbée.</p>
                        <div class="alert alert-primary">
                            <strong>📐 Formule :</strong><br>
                            <code>Parité = Valeur action société absorbée / Valeur action société absorbante</code>
                        </div>
                        <h6>Cas pratique :</h6>
                        <ul>
                            <li>Société A (Absorbante) : Valeur par action = 100 €</li>
                            <li>Société B (Absorbée) : Valeur par action = 25 €</li>
                        </ul>
                        <div class="alert alert-success">
                            <strong>✅ Calcul :</strong> Parité = 25 / 100 = <strong>0,25 (soit 1/4)</strong><br>
                            Pour 4 actions détenues dans la société B, l'actionnaire recevra 1 action nouvelle de la société A.
                        </div>
                        <div class="alert alert-info">
                            <strong>💡 Note importante :</strong> On utilise une moyenne pondérée (méthode multicritères) :<br>
                            <code>Valeur retenue = α(Valeur de rendement/PER) + β(Valeur patrimoniale/ANCC)</code>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 - CAS PRATIQUE FUSION ALPHA & BETA -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📋 5. Cas pratique : Fusion des sociétés Alpha et Beta</div>
                    <div class="card-body">
                        <h6>Étape 1 : Calcul de l'ANCC</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <td><th>Éléments</th><th>Société Alpha (€)</th><th>Société Beta (€)</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Capitaux Propres</td><td>2 000 000</td><td>500 000</td></tr>
                                    <tr><td>Plus-values latentes (Immo)</td><td>+ 300 000</td><td>+ 100 000</td></tr>
                                    <tr><td>Moins-values (Stocks)</td><td>- 50 000</td><td>- 20 000</td></tr>
                                    <tr class="table-primary"><td><strong>ANCC (Total)</strong></td><td><strong>2 250 000</strong></td><td><strong>580 000</strong></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h6 class="mt-3">Étape 2 : Détermination de la valeur par action</h6>
                        <ul>
                            <li>Alpha : 100 000 actions → Valeur par action = 2 250 000 / 100 000 = <strong>22,50 €</strong></li>
                            <li>Beta : 40 000 actions → Valeur par action = 580 000 / 40 000 = <strong>14,50 €</strong></li>
                        </ul>

                        <h6>Étape 3 : Calcul de la parité de fusion</h6>
                        <div class="alert alert-success">
                            <strong>✅ Parité = 14,50 / 22,50 = 0,6444 (29/45)</strong><br>
                            Pour 45 actions Beta, les actionnaires recevront 29 actions Alpha.
                        </div>

                        <h6>Étape 4 : Calcul des actions nouvelles à émettre</h6>
                        <div class="alert alert-primary">
                            Actions nouvelles = 40 000 × (29/45) = <strong>25 778 actions</strong>
                        </div>

                        <h6>Étape 5 : Impact sur le capital d'Alpha (Dilution)</h6>
                        <ul>
                            <li>Anciennes actions Alpha : 100 000</li>
                            <li>Nouvelles actions créées : 25 778</li>
                            <li>Total actions : 125 778</li>
                        </ul>
                        <div class="alert alert-warning">
                            <strong>⚠️ Taux de dilution = 25 778 / 125 778 = 20,49%</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 6 - Prime de fusion -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">💰 6. La Prime de Fusion et son impact</div>
                    <div class="card-body">
                        <p>Prime de fusion accordée : 10,34% (soit 1,50 € de bonus par action). Prix de cession retenu pour Beta = 16,00 €</p>
                        
                        <h6>Nouvelle parité :</h6>
                        <div class="alert alert-primary">
                            Parité = 16,00 / 22,50 = <strong>0,7111 (32/45)</strong>
                        </div>
                        
                        <h6>Nouvelles actions à créer :</h6>
                        <div class="alert alert-primary">
                            Actions nouvelles = 40 000 × (32/45) = <strong>28 444 actions</strong>
                        </div>
                        
                        <h6>Tableau comparatif :</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Indicateur</th><th>Sans prime</th><th>Avec prime (+1,50€)</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Parité</td><td>0,6444</td><td>0,7111</td></tr>
                                    <tr><td>Actions Alpha créées</td><td>25 778</td><td>28 444</td></tr>
                                    <tr><td>Dilution pour Alpha</td><td>20,49%</td><td>22,14%</td></tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-2">
                            <strong>💡 Analyse :</strong> La dilution augmente de 1,65%. Alpha doit prouver que l'intégration de Beta générera des synergies dont la VAN dépasse le coût de la prime.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 7 - Module de calcul interactif -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">🧮 7. Module de calcul interactif</div>
                    <div class="card-body">
                        <form method="POST" id="valuationForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Société Alpha (Absorbante)</h6>
                                    <div class="mb-2"><label>Capitaux Propres (€)</label><input type="number" id="cp_alpha" class="form-control" value="2000000"></div>
                                    <div class="mb-2"><label>Plus-values latentes (€)</label><input type="number" id="pv_alpha" class="form-control" value="300000"></div>
                                    <div class="mb-2"><label>Moins-values (€)</label><input type="number" id="mv_alpha" class="form-control" value="50000"></div>
                                    <div class="mb-2"><label>Nombre d'actions</label><input type="number" id="nb_actions_alpha" class="form-control" value="100000"></div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Société Beta (Absorbée)</h6>
                                    <div class="mb-2"><label>Capitaux Propres (€)</label><input type="number" id="cp_beta" class="form-control" value="500000"></div>
                                    <div class="mb-2"><label>Plus-values latentes (€)</label><input type="number" id="pv_beta" class="form-control" value="100000"></div>
                                    <div class="mb-2"><label>Moins-values (€)</label><input type="number" id="mv_beta" class="form-control" value="20000"></div>
                                    <div class="mb-2"><label>Nombre d'actions</label><input type="number" id="nb_actions_beta" class="form-control" value="40000"></div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <label>Prime de fusion (%)</label>
                                    <input type="range" id="prime" class="form-range" min="0" max="30" step="1" value="10" onchange="calculer()">
                                    <span id="prime_aff" class="badge bg-primary">10%</span>
                                </div>
                                <div class="col-12 mt-3">
                                    <button type="button" class="btn-omega w-100" onclick="calculer()">Calculer la parité et la dilution</button>
                                </div>
                            </div>
                        </form>
                        
                        <div id="resultats_valuation" class="mt-4"></div>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 MODULES COMPLÉMENTAIRES :</strong><br>
                    <a href="gestion_capital_titres.php" class="btn btn-sm btn-primary">💰 Gestion capital et titres</a>
                    <a href="analyse_scenarios.php" class="btn btn-sm btn-success">📈 Analyse de scénarios</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculer() {
    // Récupération des valeurs
    let cp_alpha = parseFloat(document.getElementById('cp_alpha').value) || 0;
    let pv_alpha = parseFloat(document.getElementById('pv_alpha').value) || 0;
    let mv_alpha = parseFloat(document.getElementById('mv_alpha').value) || 0;
    let nb_alpha = parseFloat(document.getElementById('nb_actions_alpha').value) || 1;
    
    let cp_beta = parseFloat(document.getElementById('cp_beta').value) || 0;
    let pv_beta = parseFloat(document.getElementById('pv_beta').value) || 0;
    let mv_beta = parseFloat(document.getElementById('mv_beta').value) || 0;
    let nb_beta = parseFloat(document.getElementById('nb_actions_beta').value) || 1;
    let prime = parseFloat(document.getElementById('prime').value) / 100;
    
    document.getElementById('prime_aff').innerText = (prime*100).toFixed(0) + '%';
    
    // Calcul ANC et ANCC
    let anc_alpha = cp_alpha;
    let ancc_alpha = cp_alpha + pv_alpha - mv_alpha;
    let anc_beta = cp_beta;
    let ancc_beta = cp_beta + pv_beta - mv_beta;
    
    let valeur_action_alpha = ancc_alpha / nb_alpha;
    let valeur_action_beta = ancc_beta / nb_beta;
    let valeur_avec_prime = valeur_action_beta * (1 + prime);
    
    let parite = valeur_action_beta / valeur_action_alpha;
    let parite_prime = valeur_avec_prime / valeur_action_alpha;
    
    let actions_nouvelles = nb_beta * parite;
    let actions_nouvelles_prime = nb_beta * parite_prime;
    
    let dilution = actions_nouvelles / (nb_alpha + actions_nouvelles) * 100;
    let dilution_prime = actions_nouvelles_prime / (nb_alpha + actions_nouvelles_prime) * 100;
    
    let html = `
        <div class="alert alert-success">
            <h6>📊 RÉSULTATS DE L'ÉVALUATION</h6>
            <div class="row">
                <div class="col-md-6">
                    <strong>Société Alpha :</strong><br>
                    ANC = ${anc_alpha.toLocaleString()} €<br>
                    ANCC = ${ancc_alpha.toLocaleString()} €<br>
                    Valeur par action = ${valeur_action_alpha.toFixed(2)} €
                </div>
                <div class="col-md-6">
                    <strong>Société Beta :</strong><br>
                    ANC = ${anc_beta.toLocaleString()} €<br>
                    ANCC = ${ancc_beta.toLocaleString()} €<br>
                    Valeur par action = ${valeur_action_beta.toFixed(2)} €
                </div>
            </div>
        </div>
        
        <div class="alert alert-primary">
            <h6>🔄 PARITÉ DE FUSION</h6>
            <strong>Sans prime :</strong> ${parite.toFixed(4)} (${Math.round(parite * 100)} actions Alpha pour ${Math.round(1/parite * 100)} actions Beta)<br>
            <strong>Avec prime (${(prime*100).toFixed(0)}%) :</strong> ${parite_prime.toFixed(4)}<br>
            <strong>Prix proposé pour Beta :</strong> ${valeur_avec_prime.toFixed(2)} €/action
        </div>
        
        <div class="alert alert-warning">
            <h6>📊 ACTIONS À CRÉER ET DILUTION</h6>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr><th>Indicateur</th><th>Sans prime</th><th>Avec prime</th></tr>
                </thead>
                <tbody>
                    <tr><td>Actions Alpha à créer</td><td>${actions_nouvelles.toFixed(0)}</td><td>${actions_nouvelles_prime.toFixed(0)}</td></tr>
                    <tr><td>Total actions après fusion</td><td>${(nb_alpha + actions_nouvelles).toFixed(0)}</td><td>${(nb_alpha + actions_nouvelles_prime).toFixed(0)}</td></tr>
                    <tr class="fw-bold">:<Dilution des anciens actionnaires Alpha</td><td class="text-danger">${dilution.toFixed(2)}%</td><td class="text-danger">${dilution_prime.toFixed(2)}%</td></tr>
                </tbody>
            </table>
        </div>
        
        <div class="alert alert-info">
            <strong>💡 Analyse :</strong> La prime de ${(prime*100).toFixed(0)}% augmente la dilution de <strong>${(dilution_prime - dilution).toFixed(2)}%</strong>. 
            Les actionnaires d'Alpha doivent évaluer si les synergies justifient ce coût supplémentaire.
        </div>
    `;
    
    document.getElementById('resultats_valuation').innerHTML = html;
}

// Calcul initial au chargement
window.onload = calculer;
</script>

<?php include 'inc_footer.php'; ?>
