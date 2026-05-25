<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel - Intégration fiscale, Consolidation et Fusions-Acquisitions";
$page_icon = "building";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-building"></i> Manuel d'Ingénierie Financière Avancée</h5>
                <small>Intégration fiscale, Consolidation, Fusions-Acquisitions et Stratégie de Groupe</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>La notion de Groupe et l'Intégration fiscale</li>
                        <li>Pourcentage d'Intérêt vs Pourcentage de Contrôle</li>
                        <li>Les titres constituant le capital (Actions, Certificats d'investissement)</li>
                        <li>Les méthodes de consolidation</li>
                        <li>L'Écart de première consolidation (Goodwill / Badwill)</li>
                        <li>Les retraitements de consolidation</li>
                        <li>L'impact sur le résultat consolidé</li>
                        <li>Cas pratique : Retraitement d'une marge sur stock interne</li>
                        <li>Module de calcul interactif</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 - Notion de Groupe -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🏢 1. La notion de Groupe et l'Intégration fiscale</div>
                    <div class="card-body">
                        <p>Un <strong>groupe</strong> est une entité juridique complexe composée d'une société mère et de ses filiales.</p>
                        
                        <div class="alert alert-primary">
                            <strong>📐 Intégration Fiscale :</strong> Mécanisme de "vase communicant" où la société mère agrège les résultats de toutes les filiales détenues à plus de 95%.
                        </div>
                        
                        <h6>Cas pratique : Économie d'impôt par l'intégration</h6>
                        <ul>
                            <li>Société Mère : Bénéfice de 1 000 000 €</li>
                            <li>Filiale : Perte de 400 000 €</li>
                            <li>Taux d'IS : 25%</li>
                        </ul>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <strong>SANS intégration :</strong><br>
                                    IS Mère = 1 000 000 × 25% = <strong>250 000 €</strong><br>
                                    IS Filiale = 0 €<br>
                                    <strong>Total = 250 000 €</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <strong>AVEC intégration :</strong><br>
                                    Résultat d'ensemble = 600 000 €<br>
                                    IS Groupe = 600 000 × 25% = <strong>150 000 €</strong><br>
                                    <strong>Économie = 100 000 €</strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-2">
                            <strong>✅ Avantage :</strong> Les pertes d'une filiale viennent réduire le bénéfice global du groupe → optimisation fiscale majeure.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 - Pourcentage de Contrôle vs Intérêt -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📊 2. Pourcentage d'Intérêt vs Pourcentage de Contrôle</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-header">Pourcentage de CONTRÔLE</div>
                                    <div class="card-body">
                                        <p>Détermine si la société mère peut nommer les administrateurs.</p>
                                        <p><strong>Règle :</strong> Dès qu'on dépasse 50% des droits de vote → on contrôle.</p>
                                        <p class="mb-0"><strong>Calcul :</strong> Addition des droits de vote</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-header">Pourcentage d'INTÉRÊT</div>
                                    <div class="card-body">
                                        <p>Mesure la part réelle de propriété dans les bénéfices.</p>
                                        <p><strong>Calcul :</strong> Multiplication des taux de détention</p>
                                        <p class="mb-0"><strong>Utilité :</strong> Savoir quelle part du profit nous revient</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mt-3">Cas pratique : Structure en chaîne</h6>
                        <ul>
                            <li>Mère (M) détient 60% de F1</li>
                            <li>F1 détient 70% de F2</li>
                        </ul>
                        <div class="alert alert-primary">
                            <strong>📐 Pourcentage d'intérêt de M dans F2 = 60% × 70% = 42%</strong>
                        </div>
                        <div class="alert alert-info">
                            <strong>💡 Interprétation :</strong> M possède 42% de la valeur économique de F2, mais contrôle indirectement F2.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 - Les méthodes de consolidation -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📋 3. Les méthodes de consolidation</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Méthode</th><th>Seuil de contrôle</th><th>Description</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td class="fw-bold">Intégration Globale</td><td>> 50%</td><td>Intégration 100% des actifs et passifs. Intérêts minoritaires isolés.</td></tr>
                                    <tr><td class="fw-bold">Mise en Équivalence</td><td>20% - 50%</td><td>Quote-part de la valeur de la filiale à l'actif de la mère.</td></tr>
                                    <tr><td class="fw-bold">Intégration Proportionnelle</td><td>Co-entreprise</td><td>Intégration de notre pourcentage (ex: 50% des actifs/passifs).</td></tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-primary mt-2">
                            <strong>📐 Écart de première consolidation (Goodwill) :</strong><br>
                            <code>Goodwill = Prix d'acquisition - Quote-part de l'ANCC</code>
                        </div>
                        <div class="alert alert-info">
                            <strong>💡 Goodwill :</strong> Surplus payé pour les synergies futures ou la notoriété (inscrit à l'actif).<br>
                            <strong>💡 Badwill :</strong> Achat moins cher que la valeur réelle (profit immédiat).
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 - Les retraitements de consolidation -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🔄 4. Les retraitements de consolidation</div>
                    <div class="card-body">
                        <p>Pour que les comptes consolidés soient sincères, on doit "annuler" les relations internes au groupe :</p>
                        <ul>
                            <li><strong>Créances/Dettes réciproques :</strong> Annulation des dettes et créances internes</li>
                            <li><strong>Dividendes intra-groupe :</strong> Annulation des dividendes versés entre filiales et mère</li>
                            <li><strong>Profits sur stocks :</strong> Annulation des marges internes non réalisées</li>
                        </ul>
                    </div>
                </div>

                <!-- CHAPITRE 5 - CAS PRATIQUE : Retraitement marge sur stock interne -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📋 5. Cas pratique : Retraitement d'une marge sur stock interne</div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong></p>
                        <ul>
                            <li>Société Mère (M) vend une machine à sa Filiale (F) pour 100 000 €</li>
                            <li>Coût de production pour M : 80 000 €</li>
                            <li>Marge réalisée par M : 20 000 €</li>
                            <li>À la clôture, F a toujours la machine en stock (non revendue)</li>
                            <li>Taux d'IS : 25%</li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <strong>⚠️ Problème :</strong> Le groupe a enregistré un profit de 20 000 € alors qu'il possède toujours la même machine. Le bénéfice est "interne" et doit être annulé.
                        </div>
                        
                        <h6>Écriture de retraitement :</h6>
                        <div class="alert alert-primary">
                            <strong>Débit :</strong> Compte de "Variation de stocks" (ou produit exceptionnel) : 20 000 €<br>
                            <strong>Crédit :</strong> Compte de "Stocks" (au bilan consolidé) : 20 000 €
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>💰 Annulation de l'impôt différé :</strong><br>
                            Impôt payé par M sur la marge = 20 000 × 25% = 5 000 €<br>
                            → Créance d'impôt différé à comptabiliser.
                        </div>
                        
                        <div class="alert alert-success">
                            <strong>✅ Résultat :</strong> Sans retraitement, le groupe présente un bénéfice gonflé et des actifs surestimés.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 6 - Impact sur le résultat consolidé -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📈 6. Impact sur le résultat consolidé (ROE)</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>📐 Formule du ROE :</strong><br>
                            <code>ROE = Résultat net consolidé / Capitaux propres consolidés</code>
                        </div>
                        
                        <h6>Les 3 critères de réussite d'une acquisition :</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center bg-success text-white">
                                    <div class="card-body">
                                        <h5>1. Accrétion du BPA</h5>
                                        <small>BPA après > BPA avant</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-info text-white">
                                    <div class="card-body">
                                        <h5>2. Création de valeur (EVA)</h5>
                                        <small>Rentabilité > Coût du capital</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-warning text-dark">
                                    <div class="card-body">
                                        <h5>3. Désendettement rapide</h5>
                                        <small>3 à 5 ans max</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 7 - Module de calcul interactif -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">🧮 7. Module de calcul interactif - Intérêts croisés</div>
                    <div class="card-body">
                        <form method="POST" id="groupeForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2"><label>Mère → Filiale 1 (%)</label><input type="number" id="taux1" class="form-control" value="60" step="5"></div>
                                    <div class="mb-2"><label>Filiale 1 → Filiale 2 (%)</label><input type="number" id="taux2" class="form-control" value="70" step="5"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2"><label>Filiale 2 → Filiale 3 (%)</label><input type="number" id="taux3" class="form-control" value="50" step="5"></div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn-omega" onclick="calculerInterets()">Calculer les pourcentages</button>
                            </div>
                        </form>
                        
                        <div id="resultats_groupe" class="mt-4"></div>
                    </div>
                </div>

                <!-- GLOSSAIRE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 Glossaire</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>ROE</strong> : Return On Equity (rentabilité des capitaux propres)</p>
                                <p><strong>EVA</strong> : Economic Value Added (valeur économique ajoutée)</p>
                                <p><strong>BPA</strong> : Bénéfice Par Action</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Goodwill</strong> : Écart d'acquisition (survaleur)</p>
                                <p><strong>IS</strong> : Impôt sur les Sociétés</p>
                                <p><strong>ANCC</strong> : Actif Net Comptable Corrigé</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Intégration fiscale</strong> : Consolidation fiscale du groupe</p>
                                <p><strong>Droit de vote</strong> : Pouvoir de décision</p>
                                <p><strong>Droit financier</strong> : Part dans les bénéfices</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 MODULES COMPLÉMENTAIRES :</strong><br>
                    <a href="manuel_ingenierie_financiere.php" class="btn btn-sm btn-primary">📚 Valorisation</a>
                    <a href="manuel_valeur_rendement.php" class="btn btn-sm btn-success">📈 Goodwill & DPS</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculerInterets() {
    let taux1 = parseFloat(document.getElementById('taux1').value) / 100;
    let taux2 = parseFloat(document.getElementById('taux2').value) / 100;
    let taux3 = parseFloat(document.getElementById('taux3').value) / 100;
    
    let interet_f2 = taux1 * taux2;
    let interet_f3 = interet_f2 * taux3;
    
    let html = `
        <div class="alert alert-primary">
            <h6>📊 CALCUL DES POURCENTAGES D'INTÉRÊT</h6>
            <table class="table table-bordered">
                <tr><th>Structure</th><th>Calcul</th><th>Pourcentage d'intérêt</th></tr>
                <tr><td>Mère → Filiale 1</td><td>${(taux1*100).toFixed(0)}%</td><td class="fw-bold">${(taux1*100).toFixed(0)}%</td></tr>
                <tr><td>Mère → Filiale 2</td><td>${(taux1*100).toFixed(0)}% × ${(taux2*100).toFixed(0)}%</td><td class="fw-bold">${(interet_f2*100).toFixed(2)}%</td></tr>
                <tr><td>Mère → Filiale 3</td><td>${(interet_f2*100).toFixed(2)}% × ${(taux3*100).toFixed(0)}%</td><td class="fw-bold">${(interet_f3*100).toFixed(2)}%</td></tr>
            </table>
        </div>
        
        <div class="alert alert-info">
            <strong>💡 Interprétation :</strong><br>
            La société Mère contrôle indirectement toutes les filiales. Elle détient <strong>${(interet_f3*100).toFixed(2)}%</strong> des droits financiers de la Filiale 3.
        </div>
    `;
    
    document.getElementById('resultats_groupe').innerHTML = html;
}
</script>

<?php include 'inc_footer.php'; ?>
