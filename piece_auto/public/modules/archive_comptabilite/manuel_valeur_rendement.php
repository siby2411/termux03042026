<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel - Valeur de Rendement, Goodwill & DPS";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel d'Ingénierie Financière</h5>
                <small>Valeur de Rendement, Goodwill, Augmentation de Capital et DPS</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>La Valeur de Rendement (VR)</li>
                        <li>Le Goodwill (Écart de valeur)</li>
                        <li>L'Augmentation de Capital</li>
                        <li>Le Droit Préférentiel de Souscription (DPS)</li>
                        <li>La Parité de souscription</li>
                        <li>Cas pratique : Augmentation de capital avec DPS</li>
                        <li>Module de calcul interactif</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 - Valeur de Rendement -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📈 1. La Valeur de Rendement (VR)</div>
                    <div class="card-body">
                        <p>La valeur de rendement ne regarde pas ce que l'entreprise possède (le passé), mais <strong>ce qu'elle est capable de rapporter (le futur)</strong>. Elle repose sur la capitalisation des dividendes ou du résultat net.</p>
                        
                        <div class="alert alert-primary">
                            <strong>📐 Formule :</strong><br>
                            <code>Valeur de Rendement = Dividende moyen / Taux de capitalisation</code>
                        </div>
                        
                        <h6>Cas pratique :</h6>
                        <p>Une société distribue en moyenne 5 € de dividendes par action. Le taux de rendement exigé par le marché pour ce type de risque est de 8% (0,08).</p>
                        <div class="alert alert-success">
                            <strong>✅ Calcul :</strong> VR = 5 / 0,08 = <strong>62,50 € par action</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 - Goodwill -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">✨ 2. Le Goodwill (Écart de valeur)</div>
                    <div class="card-body">
                        <p>Le <strong>Goodwill</strong> (ou survaleur) est la différence entre la valeur totale de l'entreprise (valeur de marché) et sa valeur patrimoniale (ANCC). Il représente les actifs incorporels non inscrits au bilan : <strong>notoriété, portefeuille client, savoir-faire, brevets.</strong></p>
                        
                        <div class="alert alert-primary">
                            <strong>📐 Méthode des praticiens (la plus utilisée) :</strong><br>
                            <code>Valeur Globale = (ANCC + Valeur de Rendement) / 2</code>
                        </div>
                        
                        <h6>Cas pratique :</h6>
                        <ul>
                            <li>ANCC = 540 000 €</li>
                            <li>Valeur de Rendement = 620 000 €</li>
                        </ul>
                        <div class="alert alert-success">
                            <strong>✅ Calcul :</strong> Goodwill = (540 000 + 620 000) / 2 = <strong>580 000 €</strong>
                        </div>
                        <div class="alert alert-info">
                            <strong>💡 Interprétation :</strong> Le Goodwill de 40 000 € représente la valeur de la marque et du portefeuille clients.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 - Augmentation de Capital -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">💰 3. L'Augmentation de Capital</div>
                    <div class="card-body">
                        <p>Lorsque vous augmentez le capital, vous créez de nouvelles actions. <strong>Si le prix d'émission est inférieur à la valeur réelle de l'action avant l'augmentation, les actionnaires anciens subissent une dilution.</strong></p>
                        
                        <h6>Pourquoi les entreprises font-elles cela ?</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <strong>1. Remise à niveau des fonds propres</strong><br>
                                    Suite à des pertes accumulées, le passif est trop lourd. Il faut réinjecter des liquidités pour éviter la faillite.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <strong>2. Impossibilité d'endettement</strong><br>
                                    Les banques refusent de prêter plus. L'entreprise se tourne vers les actionnaires.
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mt-3">Les 3 contraintes à arbitrer :</h6>
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Contrainte</th><th>Impact</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Perte de contrôle</td><td>L'arrivée de nouveaux actionnaires dilue le pouvoir de vote des fondateurs</td></tr>
                                <tr><td>Dilution des bénéfices</td><td>Le bénéfice total est divisé par un plus grand nombre d'actions (BPA en baisse)</td></tr>
                                <tr><td>Prix d'émission</td><td>S'il est trop bas → dilution forte. S'il est trop haut → personne ne souscrit</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CHAPITRE 4 - DPS -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🔑 4. Le Droit Préférentiel de Souscription (DPS)</div>
                    <div class="card-body">
                        <p>Le <strong>DPS</strong> permet aux anciens actionnaires de conserver leur part dans l'entreprise ou de recevoir une compensation financière s'ils décident de ne pas souscrire aux nouvelles actions.</p>
                        
                        <div class="alert alert-primary">
                            <strong>📐 Formule :</strong><br>
                            <code>DPS = Valeur de l'action avant - Valeur de l'action après (valeur théorique)</code>
                        </div>
                        
                        <div class="alert alert-success">
                            <strong>✅ Le DPS compense l'actionnaire pour la baisse de valeur de son titre.</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 - CAS PRATIQUE DÉTAILLÉ -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📋 5. Cas pratique : Augmentation de capital avec DPS</div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong></p>
                        <ul>
                            <li>Situation actuelle : 100 000 actions</li>
                            <li>Valeur de l'action avant augmentation : 150 €</li>
                            <li>Augmentation de capital : 50 000 nouvelles actions</li>
                            <li>Prix d'émission : 120 € par action nouvelle</li>
                        </ul>
                        
                        <h6 class="mt-3">Étape 1 : Valeur de l'action après opération</h6>
                        <div class="alert alert-primary">
                            Valeur initiale = 100 000 × 150 = 15 000 000 €<br>
                            Apport nouveau = 50 000 × 120 = 6 000 000 €<br>
                            Valeur totale après = 21 000 000 €<br>
                            Nombre d'actions total = 150 000<br>
                            <strong>Valeur théorique après = 21 000 000 / 150 000 = 140 €</strong>
                        </div>
                        
                        <h6>Étape 2 : Valeur du DPS</h6>
                        <div class="alert alert-success">
                            <strong>DPS = 150 - 140 = 10 €</strong><br>
                            L'actionnaire qui possède une ancienne action voit son titre perdre 10 € de valeur. En échange, il reçoit un coupon (DPS) qui vaut 10 €.
                        </div>
                        
                        <h6>Étape 3 : La parité de souscription</h6>
                        <div class="alert alert-info">
                            Ratio = 50 000 / 100 000 = <strong>1/2</strong><br>
                            Il faut donc <strong>2 anciens DPS pour souscrire à 1 action nouvelle.</strong>
                        </div>
                        
                        <h6>Vérification financière :</h6>
                        <div class="alert alert-secondary">
                            Si vous possédez 2 anciennes actions (300 € de valeur) :<br>
                            • Après opération : 2 actions × 140 € = 280 €<br>
                            • Utilisation de 2 DPS (valeur 20 €) pour acheter 1 action nouvelle à 120 €<br>
                            • Nouveau portefeuille : 3 actions × 140 € = 420 €<br>
                            • Mise de départ : 300 € + 120 € = 420 € → <strong>Équilibre parfait ✅</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 6 - Module de calcul interactif -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">🧮 6. Module de calcul interactif - Augmentation de capital</div>
                    <div class="card-body">
                        <form method="POST" id="dpsForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2"><label>Nombre d'actions existantes</label><input type="number" id="nb_actions" class="form-control" value="100000" step="1000"></div>
                                    <div class="mb-2"><label>Cours de l'action (€)</label><input type="number" id="cours_avant" class="form-control" value="150" step="10"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2"><label>Nombre d'actions nouvelles</label><input type="number" id="nb_nouvelles" class="form-control" value="50000" step="1000"></div>
                                    <div class="mb-2"><label>Prix d'émission (€)</label><input type="number" id="prix_emission" class="form-control" value="120" step="10"></div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn-omega" onclick="calculerDPS()">Calculer le DPS</button>
                            </div>
                        </form>
                        
                        <div id="resultats_dps" class="mt-4"></div>
                    </div>
                </div>

                <!-- GLOSSAIRE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 Glossaire</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>VR</strong> : Valeur de Rendement</p>
                                <p><strong>ANCC</strong> : Actif Net Comptable Corrigé</p>
                                <p><strong>DPS</strong> : Droit Préférentiel de Souscription</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Goodwill</strong> : Survaleur / écart d'acquisition</p>
                                <p><strong>BPA</strong> : Bénéfice Par Action</p>
                                <p><strong>Taux de capitalisation</strong> : Rendement exigé par le marché</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Parité</strong> : Ratio de souscription</p>
                                <p><strong>Dilution</strong> : Perte de valeur par action</p>
                                <p><strong>Prime d'émission</strong> : Différence prix d'émission / valeur nominale</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 MODULES COMPLÉMENTAIRES :</strong><br>
                    <a href="manuel_ingenierie_financiere.php" class="btn btn-sm btn-primary">📚 Manuel valorisation</a>
                    <a href="gestion_capital_titres.php" class="btn btn-sm btn-success">💰 Gestion capital et titres</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculerDPS() {
    // Récupération des valeurs
    let nb_actions = parseFloat(document.getElementById('nb_actions').value) || 1;
    let cours_avant = parseFloat(document.getElementById('cours_avant').value) || 0;
    let nb_nouvelles = parseFloat(document.getElementById('nb_nouvelles').value) || 0;
    let prix_emission = parseFloat(document.getElementById('prix_emission').value) || 0;
    
    // Calculs
    let valeur_initiale = nb_actions * cours_avant;
    let apport_nouveau = nb_nouvelles * prix_emission;
    let valeur_totale = valeur_initiale + apport_nouveau;
    let nb_total = nb_actions + nb_nouvelles;
    let cours_apres = valeur_totale / nb_total;
    let dps = cours_avant - cours_apres;
    let parite = nb_nouvelles / nb_actions;
    let duree_souscription = parite > 0 ? (1 / parite) : 0;
    
    let html = `
        <div class="alert alert-primary">
            <h6>📊 VALEUR DE L'ACTION APRÈS OPÉRATION</h6>
            <table class="table table-bordered">
                <tr><th>Élément</th><th>Calcul</th><th>Résultat</th></tr>
                <tr><td>Valeur initiale</td><td>${nb_actions.toLocaleString()} × ${cours_avant.toLocaleString()} €</td><td class="text-end">${valeur_initiale.toLocaleString()} €</td></tr>
                <tr><td>Apport nouveau</td><td>${nb_nouvelles.toLocaleString()} × ${prix_emission.toLocaleString()} €</td><td class="text-end">${apport_nouveau.toLocaleString()} €</td></tr>
                <tr><td>Valeur totale après</td><td>${valeur_initiale.toLocaleString()} + ${apport_nouveau.toLocaleString()}</td><td class="text-end">${valeur_totale.toLocaleString()} €</td></tr>
                <tr><td>Nombre total d'actions</td><td>${nb_actions.toLocaleString()} + ${nb_nouvelles.toLocaleString()}</td><td class="text-end">${nb_total.toLocaleString()}</td></tr>
                <tr class="table-primary">:<Cours théorique après</td><td>${valeur_totale.toLocaleString()} / ${nb_total.toLocaleString()}</td><td class="text-end fw-bold">${cours_apres.toFixed(2)} €</td></td>
            </table>
        </div>
        
        <div class="alert alert-success">
            <h6>🔑 DROIT PRÉFÉRENTIEL DE SOUSCRIPTION (DPS)</h6>
            <p class="mb-0"><strong>DPS = Cours avant - Cours après = ${cours_avant} - ${cours_apres.toFixed(2)} = ${dps.toFixed(2)} €</strong></p>
        </div>
        
        <div class="alert alert-info">
            <h6>📐 PARITÉ DE SOUSCRIPTION</h6>
            <p>Ratio = ${nb_nouvelles.toLocaleString()} / ${nb_actions.toLocaleString()} = ${parite.toFixed(4)}</p>
            <p><strong>Il faut ${duree_souscription.toFixed(0)} DPS pour souscrire à 1 action nouvelle.</strong></p>
        </div>
        
        <div class="alert alert-secondary">
            <h6>✅ VÉRIFICATION DE L'ÉQUILIBRE POUR UN ACTIONNAIRE</h6>
            <p><strong>Scénario :</strong> Actionnaire possédant ${duree_souscription.toFixed(0)} actions anciennes</p>
            <ul>
                <li>Valeur initiale : ${duree_souscription.toFixed(0)} × ${cours_avant} € = ${(duree_souscription * cours_avant).toFixed(2)} €</li>
                <li>Valeur après dilution : ${duree_souscription.toFixed(0)} × ${cours_apres.toFixed(2)} € = ${(duree_souscription * cours_apres).toFixed(2)} €</li>
                <li>Utilisation de ${duree_souscription.toFixed(0)} DPS (valeur ${(duree_souscription * dps).toFixed(2)} €) pour souscrire à 1 action nouvelle à ${prix_emission} €</li>
                <li>Investissement total : ${(duree_souscription * cours_avant).toFixed(2)} + ${prix_emission} = ${(duree_souscription * cours_avant + prix_emission).toFixed(2)} €</li>
                <li>Valeur du nouveau portefeuille (${(duree_souscription + 1).toFixed(0)} actions) : ${(duree_souscription + 1)} × ${cours_apres.toFixed(2)} € = ${((duree_souscription + 1) * cours_apres).toFixed(2)} €</li>
            </ul>
            <p class="fw-bold text-success">✅ L'équilibre est parfait : ${((duree_souscription + 1) * cours_apres).toFixed(2)} = ${(duree_souscription * cours_avant + prix_emission).toFixed(2)} €</p>
        </div>
    `;
    
    document.getElementById('resultats_dps').innerHTML = html;
}
</script>

<?php include 'inc_footer.php'; ?>
