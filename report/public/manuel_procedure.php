<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel de procédure - OMEGA ERP";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel de procédure OMEGA ERP</h5>
                <small>Guide pour utilisateurs non comptables - SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE DU MANUEL</strong>
                    <ol class="mb-0 mt-2">
                        <li>Introduction à la comptabilité SYSCOHADA</li>
                        <li>Premiers pas : connexion et navigation</li>
                        <li>Saisie des opérations quotidiennes</li>
                        <li>Gestion des immobilisations et amortissements</li>
                        <li>Gestion des stocks et inventaire</li>
                        <li>Gestion des salaires et charges sociales</li>
                        <li>Régularisations de fin d'exercice</li>
                        <li>Production des états financiers</li>
                        <li>Glossaire des termes comptables</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 1. Introduction à la comptabilité SYSCOHADA</div>
                    <div class="card-body">
                        <p><strong>Qu'est-ce que la comptabilité ?</strong><br>
                        La comptabilité est un outil qui permet de suivre toutes les opérations financières de votre entreprise (achats, ventes, salaires, etc.).</p>
                        <p><strong>Le principe fondamental :</strong> Chaque opération a un double effet.<br>
                        Exemple : Quand vous vendez un produit → l'argent entre dans votre banque (DÉBIT) et vous réalisez une vente (CRÉDIT).</p>
                        <div class="alert alert-success">
                            <strong>✅ À retenir :</strong> Total des DÉBITS = Total des CRÉDITS
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🔐 2. Premiers pas : connexion et navigation</div>
                    <div class="card-body">
                        <p><strong>Connexion :</strong> Rendez-vous sur http://127.0.0.1:8000/login.php<br>
                        Identifiant : admin@synthesepro.com<br>
                        Mot de passe : password</p>
                        <p><strong>Menu principal :</strong> Le menu de gauche donne accès à tous les modules.<br>
                        - <strong>Comptabilité générale</strong> : Écritures, Grand Livre, Balance<br>
                        - <strong>Gestion commerciale</strong> : Clients, Fournisseurs, Factures<br>
                        - <strong>Stocks</strong> : Articles, Entrées/Sorties, Inventaire<br>
                        - <strong>Finances</strong> : Trésorerie, Emprunts, Effets de commerce<br>
                        - <strong>Ressources humaines</strong> : Salaires, Provisions</p>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">✏️ 3. Saisie des opérations quotidiennes</div>
                    <div class="card-body">
                        <p><strong>3.1. Saisie d'une facture de vente</strong></p>
                        <ol>
                            <li>Aller dans <strong>Facturation → Facturation (réductions, frais)</strong></li>
                            <li>Choisir le client dans la liste déroulante</li>
                            <li>Sélectionner les articles vendus et la quantité</li>
                            <li>Le système calcule automatiquement TVA et total TTC</li>
                            <li>Cliquer sur "Générer la facture"</li>
                        </ol>
                        <p><strong>3.2. Enregistrer un paiement (encaissement / décaissement)</strong></p>
                        <ol>
                            <li>Aller dans <strong>Trésorerie → Rapprochement bancaire complet</strong></li>
                            <li>Choisir "Encaissement" ou "Décaissement"</li>
                            <li>Remplir le libellé, le compte et le montant</li>
                            <li>Valider</li>
                        </ol>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🏗️ 4. Gestion des immobilisations et amortissements</div>
                    <div class="card-body">
                        <p><strong>Ajouter une immobilisation :</strong></p>
                        <ol>
                            <li>Aller dans <strong>Comptabilité générale → Amortissements</strong></li>
                            <li>Onglet "Nouvelle immobilisation"</li>
                            <li>Saisir la date d'acquisition, la valeur, la durée</li>
                            <li>Valider</li>
                        </ol>
                        <p><strong>Calculer l'amortissement annuel :</strong></p>
                        <ol>
                            <li>Dans le même module, sélectionner l'immobilisation</li>
                            <li>Choisir l'exercice (année)</li>
                            <li>Sélectionner la méthode (Linéaire recommandée)</li>
                            <li>Cliquer "Calculer et enregistrer la dotation"</li>
                        </ol>
                        <div class="alert alert-warning">⚠️ Les amortissements sont calculés automatiquement selon la durée de vie du bien.</div>
                    </div>
                </div>

                <!-- CHAPITRE 5 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📦 5. Gestion des stocks et inventaire</div>
                    <div class="card-body">
                        <p><strong>Créer un article :</strong></p>
                        <ol>
                            <li>Aller dans <strong>Stocks → Gestion des articles</strong></li>
                            <li>Remplir le code, le libellé et le prix unitaire</li>
                            <li>Valider</li>
                        </ol>
                        <p><strong>Inventaire physique (fin d'exercice) :</strong></p>
                        <ol>
                            <li>Aller dans <strong>Stocks → Inventaire physique</strong></li>
                            <li>Sélectionner l'article et saisir la quantité réelle</li>
                            <li>Le système calcule l'écart et génère l'écriture comptable</li>
                        </ol>
                    </div>
                </div>

                <!-- CHAPITRE 6 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">👥 6. Gestion des salaires et charges sociales</div>
                    <div class="card-body">
                        <p><strong>Comptabiliser la paie mensuelle :</strong></p>
                        <ol>
                            <li>Aller dans <strong>Ressources humaines → Salaires & impôts</strong></li>
                            <li>Saisir le mois, l'année et le salaire brut total</li>
                            <li>Saisir l'IRPP estimé (retenu sur salaire)</li>
                            <li>Les charges sociales (CNSS, IPRES, CSS) sont calculées automatiquement</li>
                            <li>Cliquer "Comptabiliser"</li>
                        </ol>
                        <div class="alert alert-info">💡 Les écritures de paie sont automatiquement intégrées au Grand Livre.</div>
                    </div>
                </div>

                <!-- CHAPITRE 7 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">🔄 7. Régularisations de fin d'exercice</div>
                    <div class="card-body">
                        <p><strong>Objectif :</strong> Ne comptabiliser que les produits et charges de l'exercice clos.</p>
                        <p><strong>Types de régularisations :</strong></p>
                        <ul>
                            <li><strong>Charges constatées d'avance</strong> : Facture d'assurance payée pour l'année suivante</li>
                            <li><strong>Charges à payer</strong> : Facture fournisseur non encore reçue</li>
                            <li><strong>Produits à recevoir</strong> : Intérêts courus sur placement</li>
                            <li><strong>Amortissements</strong> : Constatation de la perte de valeur des immobilisations</li>
                            <li><strong>Provisions</strong> : Risques potentiels (litiges, créances douteuses)</li>
                        </ul>
                        <p><strong>Procédure :</strong></p>
                        <ol>
                            <li>Aller dans <strong>TFE → Travaux de fin d'exercice</strong></li>
                            <li>Onglet "Régularisations"</li>
                            <li>Ajouter chaque régularisation nécessaire</li>
                            <li>Contrepasser au début de l'exercice suivant</li>
                        </ol>
                    </div>
                </div>

                <!-- CHAPITRE 8 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📊 8. Production des états financiers</div>
                    <div class="card-body">
                        <p>Après toutes les régularisations, vous pouvez produire :</p>
                        <ul>
                            <li><strong>Bilan</strong> : Situation patrimoniale à la date de clôture</li>
                            <li><strong>Compte de résultat (CPC)</strong> : Performance de l'exercice</li>
                            <li><strong>Tableau des flux de trésorerie (TFT)</strong> : Mouvements de liquidités</li>
                            <li><strong>Variation des capitaux propres (TVCP)</strong> : Évolution du patrimoine net</li>
                        </ul>
                        <p><strong>Accès direct :</strong><br>
                        Menu → Comptabilité générale → Bilan / Compte de résultat / SIG</p>
                    </div>
                </div>

                <!-- CHAPITRE 9 - GLOSSAIRE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 9. Glossaire des termes comptables</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Actif</strong> : Ce que possède l'entreprise (immobilisations, stocks, trésorerie)</p>
                                <p><strong>Passif</strong> : Ce que doit l'entreprise (capitaux, dettes)</p>
                                <p><strong>Débit</strong> : Colonne de gauche d'un compte</p>
                                <p><strong>Crédit</strong> : Colonne de droite d'un compte</p>
                                <p><strong>Amortissement</strong> : Constatation de la perte de valeur d'une immobilisation</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Provision</strong> : Constatation d'un risque probable</p>
                                <p><strong>EBE</strong> : Excédent Brut d'Exploitation (richesse générée)</p>
                                <p><strong>CAF</strong> : Capacité d'Autofinancement (ressources internes)</p>
                                <p><strong>BFR</strong> : Besoin en Fonds de Roulement (besoin de financement)</p>
                                <p><strong>FR</strong> : Fonds de Roulement (marge de sécurité)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ASSISTANCE -->
                <div class="alert alert-success mt-3">
                    <i class="bi bi-headset"></i>
                    <strong>Besoin d'assistance ?</strong><br>
                    Contactez le support : support@omega-consulting.ci<br>
                    Tél : +221 78 000 00 00
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
