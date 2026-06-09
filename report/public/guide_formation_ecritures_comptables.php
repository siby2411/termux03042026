<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Guide de formation – Écritures comptables SYSCOHADA";
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
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .algo-step { background: #e9ecef; padding: 10px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #0d6efd; }
        .table-ecriture { font-family: monospace; font-size: 0.9rem; }
        .debit { color: #dc3545; font-weight: bold; }
        .credit { color: #28a745; font-weight: bold; }
        .flashcard { transition: 0.2s; cursor: pointer; }
        .flashcard:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-journal-bookmark-fill"></i> Guide de formation – Écritures comptables SYSCOHADA Révisé</h2>
                    <p>Algorithmes, matrices, cas pratiques – Formation complète pour maîtriser la comptabilité UEMOA</p>
                </div>
                <div class="card-body">

                    <!-- ==================== MODULE 1 : ALGORITHME DE RÉFLEXION ==================== -->
                    <h4 class="section-title"><i class="bi bi-diagram-3"></i> Module 1 : Algorithme de réflexion (4 étapes)</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card flashcard text-center">
                                <div class="card-body">
                                    <i class="bi bi-question-circle fs-1 text-primary"></i>
                                    <h5>Étape 1</h5>
                                    <p>Nature de l'opération<br><small>Exploitation / Inventaire / Investissement</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card flashcard text-center">
                                <div class="card-body">
                                    <i class="bi bi-arrow-left-right fs-1 text-success"></i>
                                    <h5>Étape 2</h5>
                                    <p>Sens du flux<br><small>Augmentation d'actif/charge = Débit<br>Augmentation de passif/produit = Crédit</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card flashcard text-center">
                                <div class="card-body">
                                    <i class="bi bi-menu-button-wide fs-1 text-warning"></i>
                                    <h5>Étape 3</h5>
                                    <p>Classement PCG<br><small>Quel numéro de compte selon le plan comptable ?</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card flashcard text-center">
                                <div class="card-body">
                                    <i class="bi bi-scale fs-1 text-danger"></i>
                                    <h5>Étape 4</h5>
                                    <p>Équilibre<br><small>Total Débit = Total Crédit</small></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== MODULE 2 : OPÉRATIONS COURANTES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-cart"></i> Module 2 : Opérations courantes d'exploitation</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light fw-bold">Achat de marchandises/matières</div>
                                <div class="card-body">
                                    <table class="table table-bordered table-ecriture">
                                        <thead class="table-dark"><tr><th>Compte</th><th>Intitulé</th><th>Débit</th><th>Crédit</th></tr></thead>
                                        <tbody>
                                            <tr><td class="debit">601/602</td><td>Achats</td><td class="debit">XX</td><td></td></tr>
                                            <tr><td class="debit">443</td><td>État TVA récupérable</td><td class="debit">XX</td><td></td></tr>
                                            <tr><td class="credit">401/521</td><td>Fournisseurs / Banque</td><td></td><td class="credit">XX</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light fw-bold">Vente de biens/services</div>
                                <div class="card-body">
                                    <table class="table table-bordered table-ecriture">
                                        <thead class="table-dark"><tr><th>Compte</th><th>Intitulé</th><th>Débit</th><th>Crédit</th></tr></thead>
                                        <tbody>
                                            <tr><td class="debit">411/521</td><td>Clients / Banque</td><td class="debit">XX</td><td></td></tr>
                                            <tr><td class="credit">70x</td><td>Ventes</td><td></td><td class="credit">XX</td></tr>
                                            <tr><td class="credit">443</td><td>État TVA facturée</td><td></td><td class="credit">XX</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== MODULE 3 : AMORTISSEMENTS & DÉPRÉCIATIONS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-building"></i> Module 3 : Amortissements et dépréciations</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Situation</th><th>Débit</th><th>Crédit</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Dotation annuelle aux amortissements</td><td class="debit">681 – Dotations aux amortissements</td><td class="credit">28x – Amortissements de l'immobilisation</td></tr>
                                <tr><td>Sortie d'immobilisation (VNA nette)</td><td class="debit">812 – VNA des actifs cédés</td><td class="credit">2xx – Valeur d'origine</td></tr>
                                <tr><td>Sortie d'immobilisation (annulation amort.)</td><td class="debit">28x – Amortissements cumulés</td><td class="credit">812 – VNA des actifs cédés</td></tr>
                                <tr><td>Constitution provision dépréciation stocks</td><td class="debit">855 – Dotations aux provisions stocks</td><td class="credit">39 – Dépréciation des stocks</td></tr>
                                <tr><td>Constitution provision dépréciation clients</td><td class="debit">851 – Dotations aux provisions financières</td><td class="credit">49 – Provisions pour dépréciation</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== MODULE 4 : ÉCARTS DE CONVERSION ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-currency-exchange"></i> Module 4 : Écarts de conversion de devises (comptes 476/477)</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Situation</th><th>Débit</th><th>Crédit</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Gain de change latent (créance augmente)</td><td class="debit">41x – Clients</td><td class="credit">477 – Écart de conversion (Passif)</td></tr>
                                <tr><td>Perte de change latente (dette augmente)</td><td class="debit">476 – Écart de conversion (Actif)</td><td class="credit">40x – Fournisseurs</td></tr>
                                <tr><td>Gain de change latent (dette diminue)</td><td class="debit">40x – Fournisseurs</td><td class="credit">477 – Écart de conversion (Passif)</td></tr>
                                <tr><td>Perte de change latente (créance diminue)</td><td class="debit">476 – Écart de conversion (Actif)</td><td class="credit">41x – Clients</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-2">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Note :</strong> Les comptes 476 (Actif) et 477 (Passif) ne passent jamais par le compte de résultat. Ce sont des comptes de bilan uniquement.
                    </div>

                    <!-- ==================== MODULE 5 : CLÔTURE DES COMPTES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calendar-check"></i> Module 5 : Clôture des comptes de résultats (Classe 6 & 7)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light fw-bold">Soldage des charges (Classe 6)</div>
                                <div class="card-body">
                                    <table class="table table-bordered table-ecriture">
                                        <thead class="table-dark"><tr><th>Compte</th><th>Débit</th><th>Crédit</th></tr></thead>
                                        <tbody>
                                            <tr><td>131 – Résultat net</td><td class="debit">XX</td><td></td></tr>
                                            <tr><td>6xx – Tous les comptes de charges</td><td></td><td class="credit">XX</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light fw-bold">Soldage des produits (Classe 7)</div>
                                <div class="card-body">
                                    <table class="table table-bordered table-ecriture">
                                        <thead class="table-dark"><tr><th>Compte</th><th>Débit</th><th>Crédit</th></tr></thead>
                                        <tbody>
                                            <tr><td>7xx – Tous les comptes de produits</td><td class="debit">XX</td><td></td></tr>
                                            <tr><td>131 – Résultat net</td><td></td><td class="credit">XX</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== MODULE 6 : AFFECTATION DU RÉSULTAT ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-piggy-bank"></i> Module 6 : Affectation du résultat (après AG)</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Destination du bénéfice</th><th>Débit</th><th>Crédit</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Réserve légale</td><td class="debit">131 – Résultat net</td><td class="credit">111 – Réserve légale</td></tr>
                                <tr><td>Dividendes à payer</td><td class="debit">131 – Résultat net</td><td class="credit">468 – Associés</td></tr>
                                <tr><td>Report à nouveau (bénéfice)</td><td class="debit">131 – Résultat net</td><td class="credit">12x – Report à nouveau</td></tr>
                                <tr><td>Report à nouveau (perte)</td><td class="debit">12x – Report à nouveau</td><td class="credit">131 – Résultat net</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== MODULE 7 : RÉGULARISATIONS (CUT-OFF) ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-scissors"></i> Module 7 : Régularisations – Principe du cut-off</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light fw-bold">Charges constatées d'avance (CCA)</div>
                                <div class="card-body">
                                    <p>Facture payée en N pour prestation en N+1</p>
                                    <table class="table table-bordered table-ecriture">
                                        <tr><td class="debit">486 – Charges constatées d'avance</td><td class="debit">XX</td></tr>
                                        <tr><td class="credit">6xx – Charge</td><td class="credit">XX</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light fw-bold">Produits constatés d'avance (PCA)</div>
                                <div class="card-body">
                                    <p>Facture encaissée en N pour prestation en N+1</p>
                                    <table class="table table-bordered table-ecriture">
                                        <tr><td class="debit">7xx – Produit</td><td class="debit">XX</td></tr>
                                        <tr><td class="credit">487 – Produits constatés d'avance</td><td class="credit">XX</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SIMULATEUR D'ÉCRITURES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-pencil-square"></i> Module 8 : Simulateur d'écritures comptables</h4>
                    <div class="card bg-light p-3">
                        <form method="post">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Type d'opération</label>
                                    <select name="type_operation" class="form-control" id="typeOp">
                                        <option value="achat">Achat de marchandises</option>
                                        <option value="vente">Vente de marchandises</option>
                                        <option value="amortissement">Dotation aux amortissements</option>
                                        <option value="perte_change">Perte de change latente</option>
                                        <option value="cloture_charge">Clôture des charges</option>
                                        <option value="cloture_produit">Clôture des produits</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Montant (FCFA)</label>
                                    <input type="number" name="montant" class="form-control" value="100000" required>
                                </div>
                                <div class="col-md-2 align-self-end">
                                    <button type="submit" name="generer" class="btn btn-primary">Générer l'écriture</button>
                                </div>
                            </div>
                        </form>

                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generer'])) {
                            $type = $_POST['type_operation'];
                            $montant = (float)$_POST['montant'];
                            $tva = $montant * 0.18;
                            echo '<div class="alert alert-success mt-3">';
                            echo '<h6>📝 Écriture comptable générée :</h6>';
                            echo '<div class="table-responsive"><table class="table table-bordered table-ecriture">';
                            echo '<thead class="table-dark"><tr><th>Compte</th><th>Intitulé</th><th>Débit</th><th>Crédit</th></tr></thead><tbody>';
                            
                            switch($type) {
                                case 'achat':
                                    echo "<tr><td>601</td><td>Achats de marchandises</td><td class='debit'>" . number_format($montant, 0, ',', ' ') . "</td><td></td></tr>";
                                    echo "<tr><td>443</td><td>État TVA récupérable</td><td class='debit'>" . number_format($tva, 0, ',', ' ') . "</td><td></td></tr>";
                                    echo "<tr><td>401</td><td>Fournisseurs</td><td></td><td class='credit'>" . number_format($montant + $tva, 0, ',', ' ') . "</td></tr>";
                                    break;
                                case 'vente':
                                    echo "<tr><td>411</td><td>Clients</td><td class='debit'>" . number_format($montant + $tva, 0, ',', ' ') . "</td><td></td></tr>";
                                    echo "<tr><td>701</td><td>Ventes de marchandises</td><td></td><td class='credit'>" . number_format($montant, 0, ',', ' ') . "</td></tr>";
                                    echo "<tr><td>443</td><td>État TVA facturée</td><td></td><td class='credit'>" . number_format($tva, 0, ',', ' ') . "</td></tr>";
                                    break;
                                case 'amortissement':
                                    echo "<tr><td>681</td><td>Dotations aux amortissements</td><td class='debit'>" . number_format($montant, 0, ',', ' ') . "</td><td></td></tr>";
                                    echo "<tr><td>281</td><td>Amortissements des immobilisations</td><td></td><td class='credit'>" . number_format($montant, 0, ',', ' ') . "</td></tr>";
                                    break;
                                case 'perte_change':
                                    echo "<tr><td>476</td><td>Écart de conversion (Actif)</td><td class='debit'>" . number_format($montant, 0, ',', ' ') . "</td><td></td></tr>";
                                    echo "<tr><td>401</td><td>Fournisseurs</td><td></td><td class='credit'>" . number_format($montant, 0, ',', ' ') . "</td></tr>";
                                    break;
                                case 'cloture_charge':
                                    echo "<tr><td>131</td><td>Résultat net</td><td class='debit'>" . number_format($montant, 0, ',', ' ') . "</td><td></td></tr>";
                                    echo "<tr><td>6xx</td><td>Comptes de charges</td><td></td><td class='credit'>" . number_format($montant, 0, ',', ' ') . "</td></tr>";
                                    break;
                                case 'cloture_produit':
                                    echo "<tr><td>7xx</td><td>Comptes de produits</td><td class='debit'>" . number_format($montant, 0, ',', ' ') . "</td><td></td></tr>";
                                    echo "<tr><td>131</td><td>Résultat net</td><td></td><td class='credit'>" . number_format($montant, 0, ',', ' ') . "</td></tr>";
                                    break;
                            }
                            echo '</tbody></table></div>';
                            echo '<p class="mt-2"><i class="bi bi-info-circle"></i> <strong>Principe :</strong> Total Débit = Total Crédit = ' . number_format($montant + ($type == 'achat' ? $tva : ($type == 'vente' ? $tva : 0)), 0, ',', ' ') . ' FCFA</p>';
                            echo '</div>';
                        }
                        ?>
                    </div>

                    <!-- ==================== SYNTHÈSE ==================== -->
                    <div class="alert alert-success mt-4">
                        <i class="bi bi-check-circle-fill"></i> <strong>Synthèse – Algorithme "Quick Check" pour bilan conforme :</strong><br>
                        1. Vérifier la balance : Total Débit = Total Crédit<br>
                        2. Vérifier le Cut-off : Toutes les opérations liées à l'exercice sont-elles enregistrées ?<br>
                        3. Liasse fiscale : Le résultat comptable (compte 13) sert de base au calcul de l'impôt (compte 89)
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
