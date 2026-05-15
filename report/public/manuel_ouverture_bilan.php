<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel d'ouverture du bilan - Passage N → N+1";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel d'ouverture du bilan - Exercice N → N+1</h5>
                <small>Guide pratique pour la clôture et l'ouverture des comptes</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Principes fondamentaux de la clôture d'exercice</li>
                        <li>Le report à nouveau (bénéfice/perte)</li>
                        <li>Les charges et produits constatés d'avance</li>
                        <li>Les comptes de régularisation (Classe 48)</li>
                        <li>Les contrepassations en début d'exercice</li>
                        <li>Le bilan d'ouverture N+1</li>
                        <li>Cas pratique détaillé</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">1. Principes fondamentaux de la clôture d'exercice</div>
                    <div class="card-body">
                        <p><strong>🔑 Objectif :</strong> Respecter le principe d'indépendance des exercices.</p>
                        <p>Chaque exercice comptable doit inclure uniquement les produits et charges qui le concernent.</p>
                        
                        <div class="alert alert-primary">
                            <strong>📌 Équation fondamentale :</strong><br>
                            Bilan d'ouverture N+1 = Bilan de clôture N
                        </div>
                        
                        <p>Les opérations de clôture consistent à :</p>
                        <ul>
                            <li>Constater les amortissements et provisions</li>
                            <li>Régulariser les charges et produits</li>
                            <li>Contrepasser les régularisations en début d'exercice suivant</li>
                            <li>Clôturer les comptes de gestion (classe 6 et 7)</li>
                            <li>Reporter le résultat (bénéfice ou perte)</li>
                        </ul>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">2. Le report à nouveau (comptes 110, 111, 112, 113)</div>
                    <div class="card-body">
                        <p><strong>📖 Définition :</strong> Le report à nouveau est le résultat de l'exercice précédent qui n'a pas été distribué ou affecté.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-header">BÉNÉFICE (compte 112)</div>
                                    <div class="card-body">
                                        <p>Le bénéfice est crédité au compte 112 (Report à nouveau créditeur).</p>
                                        <code class="bg-dark text-white p-1 d-block">Débit 120 - Résultat / Crédit 112 - Report à nouveau</code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-danger text-white">
                                    <div class="card-header">PERTE (compte 113)</div>
                                    <div class="card-body">
                                        <p>La perte est débitée au compte 113 (Report à nouveau débiteur).</p>
                                        <code class="bg-dark text-white p-1 d-block">Débit 113 - Report à nouveau / Crédit 120 - Résultat</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-2">
                            <strong>⚠️ Affectation du résultat :</strong> Le bénéfice peut être :
                            <ul class="mb-0">
                                <li>Distribué en dividendes (compte 457)</li>
                                <li>Mis en réserve (compte 118)</li>
                                <li>Reporté à nouveau (compte 112)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">3. Charges et produits constatés d'avance (compte 481, 482)</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">Charge constatée d'avance (481)</div>
                                    <div class="card-body">
                                        <p><strong>📌 Définition :</strong> Charge payée dans l'exercice mais se rapportant à l'exercice suivant.</p>
                                        <p><strong>📝 Exemple :</strong> Prime d'assurance payée le 01/06/N pour 12 mois.</p>
                                        <p><strong>✍️ Écriture au 31/12/N :</strong></p>
                                        <code class="bg-dark text-white p-1 d-block">Débit 481 / Crédit 471 (Compte d'attente)</code>
                                        <p class="mt-2"><strong>🔄 Contrepassation au 01/01/N+1 :</strong></p>
                                        <code class="bg-dark text-white p-1 d-block">Débit 471 / Crédit 481</code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">Produit constaté d'avance (482)</div>
                                    <div class="card-body">
                                        <p><strong>📌 Définition :</strong> Produit encaissé dans l'exercice mais se rapportant à l'exercice suivant.</p>
                                        <p><strong>📝 Exemple :</strong> Loyer perçu le 01/10/N pour 12 mois.</p>
                                        <p><strong>✍️ Écriture au 31/12/N :</strong></p>
                                        <code class="bg-dark text-white p-1 d-block">Débit 472 (Compte d'attente) / Crédit 482</code>
                                        <p class="mt-2"><strong>🔄 Contrepassation au 01/01/N+1 :</strong></p>
                                        <code class="bg-dark text-white p-1 d-block">Débit 482 / Crédit 472</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">4. Les comptes de régularisation (Classe 48)</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Compte</th><th>Intitulé</th><th>Nature</th><th>Exemple</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td class="fw-bold">481</td><td>Charges constatées d'avance</td><td>Actif</td><td>Assurance payée d'avance</td></tr>
                                    <tr><td class="fw-bold">482</td><td>Produits constatés d'avance</td><td>Passif</td><td>Loyer perçu d'avance</td></tr>
                                    <tr><td class="fw-bold">483</td><td>Charges à payer</td><td>Passif</td><td>Facture électricité non reçue</td></tr>
                                    <tr><td class="fw-bold">484</td><td>Produits à recevoir</td><td>Actif</td><td>Intérêts courus non perçus</td></tr>
                                    <tr><td class="fw-bold">485</td><td>Factures non parvenues</td><td>Passif</td><td>Achat fournisseur sans facture</td></tr>
                                    <tr><td class="fw-bold">486</td><td>Factures à établir</td><td>Actif</td><td>Vente client sans facture</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">5. Contrepassations en début d'exercice</div>
                    <div class="card-body">
                        <p><strong>📌 Principe :</strong> Les écritures de régularisation sont contrepassées le premier jour de l'exercice suivant.</p>
                        <p><strong>🔧 Pourquoi ?</strong> Pour rétablir les comptes dans leur état initial et permettre la comptabilisation normale des opérations de l'exercice N+1.</p>
                        
                        <div class="alert alert-warning">
                            <strong>⚠️ Attention :</strong> Seules les écritures de régularisation (charges/produits constatés d'avance) sont contrepassées.<br>
                            Les amortissements et provisions ne sont PAS contrepassés.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 6 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">6. Bilan d'ouverture N+1</div>
                    <div class="card-body">
                        <p><strong>📊 Composition du bilan d'ouverture :</strong></p>
                        <div class="alert alert-success">
                            <strong>ACTIF = PASSIF (toujours vérifié)</strong>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>🏦 ACTIF (Ce que possède l'entreprise)</h6>
                                <ul>
                                    <li><strong>Immobilisations (classe 2)</strong> - Valeur nette comptable</li>
                                    <li><strong>Stocks (classe 3)</strong> - Marchandises, matières premières</li>
                                    <li><strong>Créances (classe 4)</strong> - Clients, débiteurs divers</li>
                                    <li><strong>Trésorerie (classe 5)</strong> - Banque, caisse</li>
                                    <li><strong>Charges constatées d'avance (481)</strong> - Charges futures payées</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>💰 PASSIF (Ce que doit l'entreprise)</h6>
                                <ul>
                                    <li><strong>Capitaux propres (classe 1)</strong> - Capital, réserves, report à nouveau</li>
                                    <li><strong>Provisions (classe 16)</strong> - Risques et charges</li>
                                    <li><strong>Dettes (classe 4)</strong> - Fournisseurs, dettes fiscales et sociales</li>
                                    <li><strong>Produits constatés d'avance (482)</strong> - Produits futurs encaissés</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 7 - CAS PRATIQUE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">7. Cas pratique détaillé</div>
                    <div class="card-body">
                        <p><strong>📊 Données OMEGA SARL - Exercice N (2026) :</strong></p>
                        <ul>
                            <li>Capital social : 50 000 000 F</li>
                            <li>Immobilisations brutes : 51 500 000 F</li>
                            <li>Amortissements cumulés : 6 200 000 F</li>
                            <li>Ventes : 8 000 000 F</li>
                            <li>Achats : 500 000 F</li>
                            <li>Charges de personnel : 4 182 500 F</li>
                            <li>Charges diverses : 1 150 000 F</li>
                            <li>Produits financiers : 50 000 F</li>
                            <li>Trésorerie : 35 000 000 F</li>
                        </ul>
                        
                        <p><strong>🔢 Calcul du résultat :</strong></p>
                        <ul>
                            <li>Total produits : 8 050 000 F</li>
                            <li>Total charges : 12 662 500 F</li>
                            <li><strong>Résultat = -4 612 500 F (PERTE)</strong></li>
                        </ul>
                        
                        <p><strong>✍️ Écritures de clôture :</strong></p>
                        <ol>
                            <li>Contrepassation des régularisations (01/01/N+1)</li>
                            <li>Clôture des comptes de produits (débit des comptes de produits)</li>
                            <li>Clôture des comptes de charges (crédit des comptes de charges)</li>
                            <li>Report de la perte au compte 113 (Report à nouveau débiteur)</li>
                        </ol>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 ACCÈS AUX MODULES :</strong><br>
                    <a href="cloture_ouverture_exercice.php" class="btn btn-sm btn-primary">📊 Clôture/ouverture exercice</a>
                    <a href="bilan_fonctionnel.php" class="btn btn-sm btn-primary">📊 Bilan fonctionnel</a>
                    <a href="compte_resultat.php" class="btn btn-sm btn-primary">📈 Compte de résultat</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
