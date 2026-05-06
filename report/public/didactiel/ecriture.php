<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - Écritures comptables";
$page_icon = "pencil-square";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-pencil-square"></i> Module 1 : Les écritures comptables</h5>
                <small>Maîtrisez le principe fondamental de la partie double</small>
            </div>
            <div class="card-body">
                <!-- Méthodologie -->
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>📚 MÉTHODOLOGIE D'APPROCHE :</strong>
                    <p class="mt-2">Toute opération comptable se traduit par un <span class="text-danger fw-bold">DÉBIT</span> et un <span class="text-success fw-bold">CRÉDIT</span> de montant égal.</p>
                </div>
                
                <!-- Principes fondamentaux -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-danger text-white">
                                <h6><i class="bi bi-arrow-down-circle"></i> Les comptes de DÉBIT (Actif et Charges)</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>Classe 2</strong> : Immobilisations</li>
                                    <li><strong>Classe 3</strong> : Stocks</li>
                                    <li><strong>Classe 4</strong> : Créances</li>
                                    <li><strong>Classe 5</strong> : Trésorerie</li>
                                    <li><strong>Classe 6</strong> : Charges</li>
                                </ul>
                                <div class="alert alert-danger mt-2">
                                    <strong>Règle d'or :</strong> Le DÉBIT = Ce qui entre dans l'entreprise
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">
                                <h6><i class="bi bi-arrow-up-circle"></i> Les comptes de CRÉDIT (Passif et Produits)</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>Classe 1</strong> : Capitaux propres</li>
                                    <li><strong>Classe 4</strong> : Dettes fournisseurs</li>
                                    <li><strong>Classe 7</strong> : Produits</li>
                                    <li><strong>Classe 8</strong> : Comptes spéciaux</li>
                                </ul>
                                <div class="alert alert-success mt-2">
                                    <strong>Règle d'or :</strong> Le CRÉDIT = Ce qui sort de l'entreprise
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cas pratiques -->
                <h5 class="mt-4">📋 CAS PRATIQUES</h5>
                
                <!-- Cas 1 : Achat de marchandises -->
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <strong>Cas n°1 : Achat de marchandises au comptant</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong> Achat de 500.000 FCFA de marchandises payé par chèque.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-danger">
                                    <strong>📝 DÉBIT (Ce qui entre)</strong><br>
                                    <code>601 - Achats de marchandises ...... 500.000 F</code>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <strong>📝 CRÉDIT (Ce qui sort)</strong><br>
                                    <code>521 - Banque ...................... 500.000 F</code>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-2">
                            <i class="bi bi-lightbulb"></i>
                            <strong>Explication :</strong> Les marchandises entrent (Débit 601) et l'argent sort de la banque (Crédit 521).
                        </div>
                        <a href="../ecriture.php" class="btn btn-sm btn-primary">Saisir cette écriture →</a>
                    </div>
                </div>
                
                <!-- Cas 2 : Vente de prestations -->
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <strong>Cas n°2 : Vente de prestations de service</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong> Facture de prestations 750.000 FCFA, paiement par virement.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-danger">
                                    <strong>📝 DÉBIT (Ce qui entre)</strong><br>
                                    <code>521 - Banque ...................... 750.000 F</code>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <strong>📝 CRÉDIT (Ce qui sort)</strong><br>
                                    <code>703 - Prestations de services ....... 750.000 F</code>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-2">
                            <i class="bi bi-lightbulb"></i>
                            <strong>Explication :</strong> L'argent entre en banque (Débit 521) et nous générons un produit (Crédit 703).
                        </div>
                        <a href="../ecriture.php" class="btn btn-sm btn-primary">Saisir cette écriture →</a>
                    </div>
                </div>
                
                <!-- Cas 3 : Acquisition d'immobilisation -->
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <strong>Cas n°3 : Acquisition d'une immobilisation</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong> Achat d'un véhicule utilitaire 15.000.000 FCFA.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-danger">
                                    <strong>📝 DÉBIT (Ce qui entre)</strong><br>
                                    <code>253 - Véhicules utilitaires ......... 15.000.000 F</code>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <strong>📝 CRÉDIT (Ce qui sort)</strong><br>
                                    <code>521 - Banque ...................... 15.000.000 F</code>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-2">
                            <i class="bi bi-lightbulb"></i>
                            <strong>Explication :</strong> Le véhicule devient un actif (Débit 253) et la banque diminue (Crédit 521).
                        </div>
                        <a href="../ecriture.php" class="btn btn-sm btn-primary">Saisir cette écriture →</a>
                    </div>
                </div>
                
                <!-- Exercice d'application -->
                <div class="card bg-success text-white mt-4">
                    <div class="card-header">
                        <h6><i class="bi bi-trophy"></i> EXERCICE D'APPLICATION</h6>
                    </div>
                    <div class="card-body">
                        <p>Trouvez le compte Débit et Crédit pour chaque opération :</p>
                        <ol class="text-white">
                            <li>Paiement du loyer 300.000 F par chèque → (Debit ? / Crédit ?)</li>
                            <li>Apport en capital de 50.000.000 F en banque → (Debit ? / Crédit ?)</li>
                            <li>Achat de fournitures de bureau 150.000 F en espèces → (Debit ? / Crédit ?)</li>
                        </ol>
                        <details class="mt-2">
                            <summary class="btn btn-light btn-sm">Voir les réponses</summary>
                            <div class="bg-dark text-white p-2 mt-2 rounded">
                                1. Débit 613 (Location) / Crédit 521 (Banque)<br>
                                2. Débit 521 (Banque) / Crédit 101 (Capital)<br>
                                3. Débit 606 (Fournitures) / Crédit 57 (Caisse)
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
