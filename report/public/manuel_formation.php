<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel de formation SYSCOHADA";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel de formation complet - SYSCOHADA UEMOA</h5>
                <small>Guide pratique pour maîtriser la comptabilité OHADA</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📖 SOMMAIRE</strong><br>
                    1. Principes fondamentaux de la comptabilité SYSCOHADA<br>
                    2. Plan comptable général OHADA<br>
                    3. Saisie des écritures courantes<br>
                    4. Gestion des immobilisations et amortissements<br>
                    5. Régularisations de fin d'exercice<br>
                    6. Report à nouveau et clôture<br>
                    7. États financiers (Bilan, Compte de résultat, SIG)<br>
                    8. Contrôle et validation des écritures
                </div>
                
                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6><i class="bi bi-1-circle"></i> Principes fondamentaux SYSCOHADA</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>La partie double :</strong> Chaque opération se traduit par un <span class="text-danger">DÉBIT</span> et un <span class="text-success">CRÉDIT</span> de montant égal.</p>
                        <ul>
                            <li><strong>Actif</strong> (Classe 2,3,4,5) = Ce que possède l'entreprise</li>
                            <li><strong>Passif</strong> (Classe 1,6,7,8) = Ce que doit l'entreprise</li>
                            <li><strong>Charges</strong> (Classe 6) = Ce qui diminue le résultat</li>
                            <li><strong>Produits</strong> (Classe 7) = Ce qui augmente le résultat</li>
                        </ul>
                    </div>
                </div>
                
                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6><i class="bi bi-2-circle"></i> Plan comptable général OHADA</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Classe 1 : Capitaux</strong>
                                <ul>
                                    <li>101 - Capital social</li>
                                    <li>112 - Report à nouveau</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <strong>Classe 2 : Immobilisations</strong>
                                <ul>
                                    <li>231 - Constructions</li>
                                    <li>241 - Matériel informatique</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <strong>Classe 3 : Stocks</strong>
                                <ul>
                                    <li>31 - Matières premières</li>
                                    <li>37 - Marchandises</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <strong>Classe 4 : Tiers</strong>
                                <ul>
                                    <li>401 - Fournisseurs</li>
                                    <li>411 - Clients</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <strong>Classe 5 : Trésorerie</strong>
                                <ul>
                                    <li>521 - Banque</li>
                                    <li>57 - Caisse</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <strong>Classe 6-7 : Charges & Produits</strong>
                                <ul>
                                    <li>661 - Salaires</li>
                                    <li>703 - Prestations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6><i class="bi bi-3-circle"></i> Saisie des écritures (Pratique)</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Cas concret :</strong> Vente de prestation 125.000 XAF</p>
                        <pre class="bg-dark text-white p-3 rounded">
Débit  : 521 Banque ........ 125.000 F
Crédit : 703 Prestations .... 125.000 F
                        </pre>
                        <p><strong>Comment faire dans l'application ?</strong></p>
                        <ol>
                            <li>Aller dans <a href="ecriture.php">Saisie d'écriture</a></li>
                            <li>Remplir la date et le libellé</li>
                            <li>Entrer 521 dans le champ DÉBIT</li>
                            <li>Entrer 703 dans le champ CRÉDIT</li>
                            <li>Saisir le montant et valider</li>
                        </ol>
                    </div>
                </div>
                
                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6><i class="bi bi-4-circle"></i> Amortissements</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Principe :</strong> Constater la perte de valeur des immobilisations sur leur durée de vie.</p>
                        <p><strong>Calcul linéaire :</strong> (Valeur brute × Taux) / 100</p>
                        <p><strong>Exemple :</strong> Matériel info 500.000 F sur 5 ans → 100.000 F par an</p>
                        <p><a href="amortissements_complet.php" class="btn btn-sm btn-primary">👉 Gérer les amortissements</a></p>
                    </div>
                </div>
                
                <!-- CHAPITRE 5 -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6><i class="bi bi-5-circle"></i> Régularisations de fin d'exercice</h6>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li><strong>Charges constatées d'avance :</strong> Prime d'assurance payée pour l'année suivante → Compte 481</li>
                            <li><strong>Produits constatés d'avance :</strong> Loyer perçu pour l'année suivante → Compte 482</li>
                            <li><strong>Charges à payer :</strong> Facture fournisseur non reçue → Compte 483 + 401</li>
                            <li><strong>Produits à recevoir :</strong> Facture client non émise → Compte 484 + 411</li>
                        </ul>
                        <p><a href="regularisations.php" class="btn btn-sm btn-primary">👉 Gérer les régularisations</a></p>
                    </div>
                </div>
                
                <!-- CHAPITRE 6 -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6><i class="bi bi-6-circle"></i> Report à nouveau</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Objectif :</strong> Reporter le bénéfice ou la perte de l'exercice clos sur l'exercice suivant.</p>
                        <p><strong>Comptes utilisés :</strong> 112 (Bénéfice reporté), 113 (Perte reportée)</p>
                        <p><a href="report_nouveau.php" class="btn btn-sm btn-primary">👉 Effectuer le report à nouveau</a></p>
                    </div>
                </div>
                
                <!-- CHAPITRE 7 -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6><i class="bi bi-7-circle"></i> États financiers</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="bilan.php" class="btn btn-outline-primary w-100 mb-2">📊 Bilan</a>
                            </div>
                            <div class="col-md-4">
                                <a href="compte_resultat.php" class="btn btn-outline-primary w-100 mb-2">📈 Compte de résultat</a>
                            </div>
                            <div class="col-md-4">
                                <a href="sig.php" class="btn btn-outline-primary w-100 mb-2">📉 Tableau SIG</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- BONNES PRATIQUES -->
                <div class="alert alert-success mt-3">
                    <i class="bi bi-star-fill"></i>
                    <strong>Bonnes pratiques comptables :</strong>
                    <ul class="mb-0 mt-2">
                        <li>✓ Toujours vérifier l'équilibre Débit = Crédit</li>
                        <li>✓ Justifier chaque écriture par une pièce justificative</li>
                        <li>✓ Effectuer les rapprochements bancaires mensuellement</li>
                        <li>✓ Calculer les amortissements chaque fin d'exercice</li>
                        <li>✓ Contrepasser les régularisations en début d'exercice suivant</li>
                    </ul>
                </div>
                
                <!-- CONTACT SUPPORT -->
                <div class="text-center mt-4">
                    <p><strong>© 2026 OMEGA INFORMATIQUE CONSULTING - Mohamet Siby</strong></p>
                    <p>Support technique : support@omega-consulting.ci</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
