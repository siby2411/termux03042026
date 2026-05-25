<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel - Restructuration, Désinvestissement et Fusions";
$page_icon = "arrow-left-right";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-left-right"></i> Manuel de Restructuration Stratégique</h5>
                <small>Désinvestissements, Provisionnement, Fusions, Scissions et Régimes fiscaux</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Les Désinvestissements et le Provisionnement des titres</li>
                        <li>Le cas des monnaies fondantes (inflation et ajustement)</li>
                        <li>Les opérations de restructuration (Fusion, Scission, Apport Partiel d'Actif)</li>
                        <li>Le Régime de faveur fiscal</li>
                        <li>Le piège de la fusion : réévaluation et fiscalité</li>
                        <li>La soulte et son traitement fiscal</li>
                        <li>Cas pratique : Fusion avec réévaluation des actifs</li>
                        <li>Module de calcul interactif</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 - Désinvestissements -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📉 1. Les Désinvestissements et le Provisionnement des titres</div>
                    <div class="card-body">
                        <p>L'enjeu est de <strong>ne pas "surévaluer" au bilan les titres de filiales qui perdent de la valeur.</strong></p>
                        
                        <div class="alert alert-primary">
                            <strong>📐 Provisionnement des titres :</strong><br>
                            Si la valeur réelle d'une filiale (valeur de mise en équivalence) devient inférieure à sa valeur d'achat au bilan, l'entreprise doit constater une <strong>dépréciation</strong> (dotation aux provisions).
                        </div>
                        
                        <h6>Cas pratique :</h6>
                        <ul>
                            <li>Valeur d'achat de la filiale : 1 000 000 €</li>
                            <li>Valeur de mise en équivalence (réelle) : 700 000 €</li>
                            <li>Taux d'IS : 25%</li>
                        </ul>
                        <div class="alert alert-warning">
                            <strong>⚠️ Dépréciation à constater : 300 000 €</strong><br>
                            Écriture : Débit 686 (Dotations aux provisions) / Crédit 29 (Dépréciation des titres)
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 - Monnaies fondantes -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">💱 2. Le cas des monnaies fondantes (inflation)</div>
                    <div class="card-body">
                        <p>Si une filiale est dans un pays à forte inflation (monnaie qui perd de sa valeur), la valeur comptable de la filiale en monnaie locale semble monter, mais sa valeur réelle diminue.</p>
                        
                        <div class="alert alert-danger">
                            <strong>⚠️ Risque :</strong> Il faut ajuster les provisions pour ne pas être trompé par l'inflation locale.
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>💡 Solution :</strong> Utiliser un taux de conversion ajusté (monnaie fonctionnelle) et constater les écarts de conversion.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 - Opérations de restructuration -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🔄 3. Les opérations de restructuration</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <td><th>Opération</th><th>Définition</th><th>Objectif</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td class="fw-bold">Fusion / Absorption</td><td>Une société est absorbée par une autre. Elle disparaît.</td><td>Concentration, économies d'échelle</td></tr>
                                    <tr><td class="fw-bold">Scission</td><td>Une société se divise en deux ou plusieurs entités nouvelles.</td><td>Recentrage stratégique sur un métier</td></tr>
                                    <tr><td class="fw-bold">Apport Partiel d'Actif (APA)</td><td>Une société apporte une branche d'activité à une autre.</td><td>Externaliser une activité sans disparaître</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 - Régime de faveur fiscal -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">💰 4. Le "Régime de faveur" fiscal</div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <strong>✅ Principe fondamental :</strong><br>
                            En droit commun, une fusion générerait un impôt massif (taxation des plus-values latentes).<br>
                            <strong>Le régime de faveur permet de différer l'imposition :</strong> les plus-values ne sont pas taxées au moment de la fusion, à condition que les sociétés conservent la valeur comptable des actifs transférés.
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>💡 C'est ce qui rend les fusions possibles en pratique !</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 - Le piège de la fusion -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">⚠️ 5. Le piège de la fusion (réévaluation et fiscalité)</div>
                    <div class="card-body">
                        <p>Lors d'une fusion, on doit souvent <strong>"réévaluer"</strong> les actifs de la société absorbée à leur valeur réelle (valeur vénale) avant de calculer la parité.</p>
                        
                        <div class="alert alert-danger">
                            <strong>⚠️ Le piège :</strong> Si l'on réévalue l'actif, on fait apparaître une plus-value. Si le régime de faveur n'est pas appliqué correctement, cette plus-value est immédiatement taxable.
                        </div>
                        
                        <div class="alert alert-warning">
                            <strong>📌 La règle d'or :</strong> L'actionnaire de la société absorbée reçoit des titres de la société absorbante. Cette opération est neutre fiscalement pour lui, <strong>sauf s'il reçoit une "soulte"</strong> (paiement en cash en complément des titres), qui est alors taxable.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 6 - La soulte -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">💵 6. La soulte et son traitement fiscal</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📐 Définition :</strong> La soulte est le versement en espèces qui complète l'échange d'actions lors d'une fusion, pour équilibrer la parité.
                        </div>
                        
                        <div class="alert alert-danger">
                            <strong>⚠️ Fiscalité de la soulte :</strong> La soulte est <strong>immédiatement taxable</strong> pour l'actionnaire de la société absorbée, contrairement aux titres reçus.
                        </div>
                        
                        <h6>Seuils de tolérance :</h6>
                        <ul>
                            <li>Si soulte < 10% de la valeur nominale des titres reçus → régime de faveur maintenu</li>
                            <li>Si soulte > 10% → la totalité de la plus-value est imposable immédiatement</li>
                        </ul>
                    </div>
                </div>

                <!-- CHAPITRE 7 - CAS PRATIQUE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📋 7. Cas pratique : Fusion avec réévaluation des actifs</div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong></p>
                        <ul>
                            <li>Société A (absorbante) : 100 000 actions, valeur 150 €/action</li>
                            <li>Société B (absorbée) : 40 000 actions, valeur comptable 80 €/action</li>
                            <li>Actif de B a une plus-value latente de 400 000 € (immobilier)</li>
                            <li>Valeur réelle de B : 100 €/action après réévaluation</li>
                            <li>Parité proposée : 1 action A pour 1 action B</li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <strong>⚠️ Problème fiscal :</strong> La plus-value latente de 400 000 € devient imposable si le régime de faveur n'est pas appliqué.
                        </div>
                        
                        <div class="alert alert-success">
                            <strong>✅ Solution :</strong> Appliquer le régime de faveur pour différer l'imposition de la plus-value.
                        </div>
                        
                        <div class="alert alert-info mt-2">
                            <strong>📊 Calcul des actions à émettre :</strong><br>
                            Valeur de B = 40 000 × 100 € = 4 000 000 €<br>
                            Valeur de A = 150 € par action<br>
                            Actions à émettre = 4 000 000 / 150 = <strong>26 667 actions</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 8 - Module de calcul interactif -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">🧮 8. Module de calcul interactif - Fusion et soulte</div>
                    <div class="card-body">
                        <form method="POST" id="fusionForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Société A (Absorbante)</h6>
                                    <div class="mb-2"><label>Nombre d'actions</label><input type="number" id="nb_actions_a" class="form-control" value="100000" step="1000"></div>
                                    <div class="mb-2"><label>Valeur par action (€)</label><input type="number" id="valeur_action_a" class="form-control" value="150" step="10"></div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Société B (Absorbée)</h6>
                                    <div class="mb-2"><label>Nombre d'actions</label><input type="number" id="nb_actions_b" class="form-control" value="40000" step="1000"></div>
                                    <div class="mb-2"><label>Valeur par action (€)</label><input type="number" id="valeur_action_b" class="form-control" value="100" step="10"></div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <div class="mb-2"><label>Parité proposée (X actions A pour Y actions B)</label>
                                        <div class="row">
                                            <div class="col-6"><input type="number" id="parite_num" class="form-control" placeholder="Numérateur" value="1"></div>
                                            <div class="col-6"><input type="number" id="parite_den" class="form-control" placeholder="Dénominateur" value="1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn-omega" onclick="calculerFusion()">Calculer la fusion</button>
                            </div>
                        </form>
                        
                        <div id="resultats_fusion" class="mt-4"></div>
                    </div>
                </div>

                <!-- SYNTHÈSE FINALE -->
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white">📖 Synthèse du Parcours Pédagogique</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card text-center bg-primary text-white h-100">
                                    <div class="card-body">
                                        <h5>Valorisation</h5>
                                        <small>ANC, ANCC, PER, Goodwill</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-success text-white h-100">
                                    <div class="card-body">
                                        <h5>Augmentation Capital</h5>
                                        <small>DPS, Droit d'attribution</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-warning text-dark h-100">
                                    <div class="card-body">
                                        <h5>Consolidation</h5>
                                        <small>Intégration fiscale, retraitements</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-danger text-white h-100">
                                    <div class="card-body">
                                        <h5>Restructuration</h5>
                                        <small>Fusion, scission, soulte</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 MODULES COMPLÉMENTAIRES :</strong><br>
                    <a href="manuel_ingenierie_financiere.php" class="btn btn-sm btn-primary">📚 Valorisation</a>
                    <a href="manuel_valeur_rendement.php" class="btn btn-sm btn-success">📈 Goodwill & DPS</a>
                    <a href="manuel_consolidation_groupe.php" class="btn btn-sm btn-warning">🏢 Consolidation</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculerFusion() {
    let nbA = parseFloat(document.getElementById('nb_actions_a').value) || 1;
    let valA = parseFloat(document.getElementById('valeur_action_a').value) || 1;
    let nbB = parseFloat(document.getElementById('nb_actions_b').value) || 1;
    let valB = parseFloat(document.getElementById('valeur_action_b').value) || 1;
    let pariteNum = parseFloat(document.getElementById('parite_num').value) || 1;
    let pariteDen = parseFloat(document.getElementById('parite_den').value) || 1;
    
    let valeurTotaleA = nbA * valA;
    let valeurTotaleB = nbB * valB;
    let pariteReelle = valB / valA;
    let pariteProposee = pariteNum / pariteDen;
    
    let actionsAEmettre = nbB * pariteProposee;
    let soulte = 0;
    let soulteMessage = "";
    
    if (pariteProposee < pariteReelle) {
        // Soulte en faveur des actionnaires de B
        let valeurTheorique = nbB * pariteReelle * valA;
        let valeurProposee = nbB * pariteProposee * valA;
        soulte = valeurTheorique - valeurProposee;
        soulteMessage = `Soulte à verser aux actionnaires de B : ${soulte.toFixed(2)} € (${(soulte / valeurTheorique * 100).toFixed(2)}% de la valeur)`;
    } else if (pariteProposee > pariteReelle) {
        // Soulte en faveur des actionnaires de A
        let valeurTheorique = nbB * pariteReelle * valA;
        let valeurProposee = nbB * pariteProposee * valA;
        soulte = valeurProposee - valeurTheorique;
        soulteMessage = `Soulte à verser aux actionnaires de A : ${soulte.toFixed(2)} € (${(soulte / valeurTheorique * 100).toFixed(2)}% de la valeur)`;
    } else {
        soulteMessage = "Pas de soulte - parité exacte";
    }
    
    let nbTotalActions = nbA + actionsAEmettre;
    let dilution = (actionsAEmettre / nbTotalActions) * 100;
    
    let html = `
        <div class="alert alert-primary">
            <h6>📊 ÉVALUATION DES SOCIÉTÉS</h6>
            <table class="table table-bordered">
                <tr><th>Société</th><th>Nombre d'actions</th><th>Valeur par action</th><th>Valeur totale</th></tr>
                <tr><td>A (absorbante)</td><td class="text-end">${nbA.toLocaleString()}</td><td class="text-end">${valA.toLocaleString()} €</td><td class="text-end">${valeurTotaleA.toLocaleString()} €</td></tr>
                <tr><td>B (absorbée)</td><td class="text-end">${nbB.toLocaleString()}</td><td class="text-end">${valB.toLocaleString()} €</td><td class="text-end">${valeurTotaleB.toLocaleString()} €</td></tr>
            </table>
        </div>
        
        <div class="alert alert-success">
            <h6>🔄 PARITÉ DE FUSION</h6>
            <p>Parité réelle : 1 action A pour ${(1/pariteReelle).toFixed(4)} actions B (soit ${pariteReelle.toFixed(4)} action A par action B)</p>
            <p>Parité proposée : ${pariteNum} action(s) A pour ${pariteDen} action(s) B (soit ${pariteProposee.toFixed(4)} action A par action B)</p>
            <p class="fw-bold">Actions A à émettre : ${actionsAEmettre.toFixed(0)}</p>
        </div>
        
        <div class="alert alert-warning">
            <h6>💰 SOULTE</h6>
            <p>${soulteMessage}</p>
            ${soulte > 0 ? `<p class="text-danger fw-bold">⚠️ La soulte de ${(soulte / (nbB * valB) * 100).toFixed(2)}% est taxable !</p>` : ''}
        </div>
        
        <div class="alert alert-info">
            <h6>📊 IMPACT SUR LE CAPITAL</h6>
            <p>Actions A avant fusion : ${nbA.toLocaleString()}</p>
            <p>Actions A créées : ${actionsAEmettre.toFixed(0)}</p>
            <p>Total actions après fusion : ${nbTotalActions.toFixed(0)}</p>
            <p class="fw-bold">Dilution des anciens actionnaires A : ${dilution.toFixed(2)}%</p>
        </div>
        
        <div class="alert alert-secondary">
            <strong>💡 Recommandation fiscale :</strong><br>
            ${soulte > (nbB * valB * 0.1) ? 
                '⚠️ La soulte dépasse 10% de la valeur des titres reçus → RISQUE DE TAXATION IMMÉDIATE' : 
                '✅ La soulte est inférieure à 10% → Régime de faveur applicable'}
        </div>
    `;
    
    document.getElementById('resultats_fusion').innerHTML = html;
}
</script>

<?php include 'inc_footer.php'; ?>
