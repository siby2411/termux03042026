<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Guide des écritures comptables – SYSCOHADA Révisé";
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
        .compte-t { font-family: monospace; font-size: 1.1rem; }
        .ecriture-card { transition: 0.2s; margin-bottom: 25px; }
        .ecriture-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .debit { color: #dc3545; font-weight: bold; }
        .credit { color: #28a745; font-weight: bold; }
        .table-t { font-family: monospace; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-journal-bookmark-fill"></i> Guide des écritures comptables – SYSCOHADA Révisé</h2>
                    <p>Formation pédagogique : comptes en T, immobilisations, VMP, provisions, régularisations, TAFIRE</p>
                </div>
                <div class="card-body">

                    <!-- ==================== CHAPITRE 1 : IMMOBILISATIONS & AMORTISSEMENTS ==================== -->
                    <h4 class="section-title"><i class="bi bi-building"></i> 1. Immobilisations et amortissements</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Dotation aux amortissements</div>
                                <div class="card-body">
                                    <p>L'amortissement constate la perte de valeur irréversible d'un actif.</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t w-75 mx-auto">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">811 – Dotations aux amortissements</td><td class="debit">200 000</td><td></td></tr>
                                                <tr><td class="credit">28 – Amortissements des immobilisations</td><td></td><td class="credit">200 000</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="mt-2"><i class="bi bi-info-circle"></i> <strong>Impact :</strong> Charge non décaissable → diminue le résultat net.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Amortissement par composants</div>
                                <div class="card-body">
                                    <p>Exemple : Bâtiment 100M (structure 80M/40 ans, composant 20M/10 ans)</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">211 – Bâtiment (structure)</td><td class="debit">80 000 000</td><td></td></tr>
                                                <tr><td class="debit">211 – Bâtiment (composant)</td><td class="debit">20 000 000</td><td></td></tr>
                                                <tr><td class="credit">404 – Fournisseurs d'immobilisations</td><td></td><td class="credit">100 000 000</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p><strong>Dotation annuelle :</strong> Structure 2M + Composant 2M = 4M FCFA</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 2 : VMP ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-graph-up"></i> 2. Cession de Valeurs Mobilières de Placement (VMP)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Vente de titres (gain)</div>
                                <div class="card-body">
                                    <p>Vente de 100 actions (achat 500 000, vente 600 000) → Gain 100 000</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">521 – Banque</td><td class="debit">600 000</td><td></td></tr>
                                                <tr><td class="credit">50 – VMP</td><td></td><td class="credit">500 000</td></tr>
                                                <tr><td class="credit">877 – Produits nets sur cessions VMP</td><td></td><td class="credit">100 000</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p><i class="bi bi-info-circle"></i> <strong>Impact :</strong> Augmentation de la trésorerie et du résultat net.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 3 : PROVISIONS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-shield-exclamation"></i> 3. Provisions pour dépréciation</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Provision pour dépréciation des stocks</div>
                                <div class="card-body">
                                    <p>Stock initial 2 000 000, perte de valeur estimée 200 000</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">855 – Dotations aux provisions stocks</td><td class="debit">200 000</td><td></td></tr>
                                                <tr><td class="credit">39 – Dépréciation des stocks</td><td></td><td class="credit">200 000</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p><i class="bi bi-info-circle"></i> <strong>Principe de prudence :</strong> anticiper les pertes probables.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Provision pour dépréciation clients</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">851 – Dotations aux provisions financières</td><td class="debit">XX</td><td></td></tr>
                                                <tr><td class="credit">49 – Provisions pour dépréciation clients</td><td></td><td class="credit">XX</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 4 : EFFETS DE COMMERCE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-file-text"></i> 4. Effets de commerce</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Effet à recevoir (client)</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">413 – Clients, effets à recevoir</td><td class="debit">300 000</td><td></td></tr>
                                                <tr><td class="credit">411 – Clients</td><td></td><td class="credit">300 000</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Effet à payer (fournisseur)</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">401 – Fournisseurs</td><td class="debit">XX</td><td></td></tr>
                                                <tr><td class="credit">403 – Fournisseurs, effets à payer</td><td></td><td class="credit">XX</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 5 : RÉGULARISATIONS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calendar"></i> 5. Régularisations (principe d'indépendance des exercices)</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Type d'opération</th><th>Explication</th><th>Écriture (31/12)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Charge constatée d'avance</td><td>Paiement en N pour prestation N+1</td><td>Débit 486 / Crédit 6XX</td></tr>
                                <tr><td>Produit constaté d'avance</td><td>Encaissement en N pour prestation N+1</td><td>Débit 7XX / Crédit 487</td></tr>
                                <tr><td>Charge à payer (FNP)</td><td>Facture électricité décembre non reçue</td><td>Débit 6XX / Crédit 408</td></tr>
                                <tr><td>Produit à recevoir (FNR)</td><td>Facture à établir</td><td>Débit 418 / Crédit 7XX</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="example p-3 bg-light rounded mt-2">
                        <strong>Cas pratique :</strong> Facture électricité 50 000 FCFA (décembre) non parvenue → Écriture : Débit 621 / Crédit 408
                    </div>

                    <!-- ==================== CHAPITRE 6 : TVA ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-percent"></i> 6. Traitement de la TVA (Taux 18%)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Achat de marchandises HT 1 000 000</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">601 – Achats</td><td class="debit">1 000 000</td><td></td></tr>
                                                <tr><td class="debit">445 – TVA récupérable</td><td class="debit">180 000</td><td></td></tr>
                                                <tr><td class="credit">401 – Fournisseurs</td><td></td><td class="credit">1 180 000</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Vente de marchandises HT 1 500 000</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">411 – Clients</td><td class="debit">1 770 000</td><td></td></tr>
                                                <tr><td class="credit">701 – Ventes</td><td></td><td class="credit">1 500 000</td></tr>
                                                <tr><td class="credit">443 – TVA facturée</td><td></td><td class="credit">270 000</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 7 : TAFIRE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-table"></i> 7. TAFIRE – Tableau Financier des Ressources et Emplois</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>📊 Construction du TAFIRE (exemple)</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-dark">
                                                <tr><th>Rubriques</th><th>Montant (FCFA)</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr class="table-primary"><td colspan="2">I. RESSOURCES</td></tr>
                                                <tr><td>Résultat Net</td><td class="text-end">1 000 000</td></tr>
                                                <tr><td>+ Dotations aux amortissements</td><td class="text-end">400 000</td></tr>
                                                <tr class="table-success"><td>Capacité d'Autofinancement (CAF)</td><td class="text-end fw-bold">1 400 000</td></tr>
                                                <tr class="table-primary"><td colspan="2">II. EMPLOIS</td></tr>
                                                <tr><td>- Acquisitions d'immobilisations</td><td class="text-end">(600 000)</td></tr>
                                                <tr><td>- Remboursements d'emprunts</td><td class="text-end">(200 000)</td></tr>
                                                <tr><td>- Augmentation du BFR</td><td class="text-end">(100 000)</td></tr>
                                                <tr class="table-info"><td>Total des emplois</td><td class="text-end">(900 000)</td></tr>
                                                <tr class="table-success"><td>III. VARIATION DE TRÉSORERIE (CAF - Emplois)</td><td class="text-end fw-bold">500 000</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="mt-2"><i class="bi bi-info-circle"></i> <strong>Analyse :</strong> La CAF (1 400 000) est la ressource générée par l'activité. Après investissements, remboursements et BFR, il reste 500 000 de cash supplémentaire.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 8 : IMPÔT SUR LES SOCIÉTÉS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 8. Détermination de l'impôt sur les sociétés (IS)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Calcul IS (taux 25%)</div>
                                <div class="card-body">
                                    <p>Résultat comptable : 5 000 000<br>
                                    Réintégrations : +200 000 | Déductions : -300 000<br>
                                    <strong>Résultat fiscal : 4 900 000</strong><br>
                                    Impôt (25%) : <strong>1 225 000</strong></p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-t">
                                            <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">89 – Impôt sur les bénéfices</td><td class="debit">1 225 000</td><td></td></tr>
                                                <tr><td class="credit">449 – État, impôts sur bénéfices</td><td></td><td class="credit">1 225 000</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 9 : SYNTHÈSE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-clipboard-check"></i> 9. Processus de clôture (ordre logique)</h4>
                    <ol class="list-group list-group-numbered">
                        <li class="list-group-item">Inventaire physique – vérification des stocks</li>
                        <li class="list-group-item">Travaux d'inventaire – amortissements, provisions, régularisations</li>
                        <li class="list-group-item">Arrêté des comptes – balance après inventaire</li>
                        <li class="list-group-item">Détermination du résultat – comptable puis fiscal</li>
                        <li class="list-group-item">Comptabilisation de l'IS – écriture au compte 89</li>
                        <li class="list-group-item">Établissement des états financiers – Bilan, CR, TAFIRE</li>
                    </ol>

                    <div class="alert alert-success mt-4">
                        <i class="bi bi-check-circle-fill"></i> <strong>Synthèse :</strong> La maîtrise des comptes en T, des régularisations et du TAFIRE est la clé d'une comptabilité conforme SYSCOHADA et d'une analyse financière pertinente.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
