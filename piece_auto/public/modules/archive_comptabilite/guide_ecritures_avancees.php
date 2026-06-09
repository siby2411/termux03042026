<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Guide avancé – Écritures comptables SYSCOHADA";
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
        .ecriture-card { transition: 0.2s; margin-bottom: 25px; border-left: 5px solid #0d6efd; }
        .ecriture-card:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .debit { color: #dc3545; font-weight: bold; }
        .credit { color: #28a745; font-weight: bold; }
        .table-t { font-family: monospace; font-size: 0.9rem; }
        .problematique { background: #fff3cd; border-left: 5px solid #ffc107; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-journal-bookmark-fill"></i> Guide avancé – Écritures comptables SYSCOHADA</h2>
                    <p>Opérations en devises, crédit-bail, inventaire permanent, comptabilité analytique (Classe 9)</p>
                </div>
                <div class="card-body">

                    <!-- ==================== CHAPITRE 1 : OPÉRATIONS EN DEVISES ==================== -->
                    <h4 class="section-title"><i class="bi bi-currency-exchange"></i> 1. Opérations en devises (convertibilité)</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Scénario : Importation en USD</div>
                                <div class="card-body">
                                    <p>Facture fournisseur étranger : 10 000 USD – Cours à l'achat : 600 FCFA/USD – Cours au 31/12 : 620 FCFA/USD</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Écriture à l'achat</h6>
                                            <table class="table table-bordered table-t w-100">
                                                <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                                <tbody>
                                                    <tr><td class="debit">601 – Achats de marchandises</td><td class="debit">6 000 000</td><td></td></tr>
                                                    <tr><td class="credit">401 – Fournisseurs étrangers</td><td></td><td class="credit">6 000 000</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Écriture de régularisation (31/12) – Perte de change</h6>
                                            <p>Calcul : (620 - 600) × 10 000 = 200 000 FCFA</p>
                                            <table class="table table-bordered table-t w-100">
                                                <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                                <tbody>
                                                    <tr><td class="debit">674 – Pertes de change</td><td class="debit">200 000</td><td></td></tr>
                                                    <tr><td class="credit">401 – Fournisseurs étrangers</td><td></td><td class="credit">200 000</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mt-2">
                                        <i class="bi bi-info-circle"></i> <strong>Principe :</strong> Les créances et dettes en devises doivent être réévaluées au cours de clôture. La différence est comptabilisée en perte (674) ou gain (774) de change.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 2 : CRÉDIT-BAIL (LEASING) ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-truck"></i> 2. Crédit-Bail (Lease) – Retraitement économique</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Scénario : Contrat de leasing</div>
                                <div class="card-body">
                                    <p>Bien : 10 000 000 FCFA – Redevance annuelle : 2 500 000 FCFA (dont 500 000 d'intérêts)</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Enregistrement de l'immobilisation</h6>
                                            <table class="table table-bordered table-t w-100">
                                                <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                                <tbody>
                                                    <tr><td class="debit">21 – Immobilisations</td><td class="debit">10 000 000</td><td></td></tr>
                                                    <tr><td class="credit">17 – Dette financière (leasing)</td><td></td><td class="credit">10 000 000</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Paiement de la redevance annuelle</h6>
                                            <table class="table table-bordered table-t w-100">
                                                <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                                <tbody>
                                                    <tr><td class="debit">17 – Dette financière (leasing)</td><td class="debit">2 000 000</td><td></td></tr>
                                                    <tr><td class="debit">672 – Intérêts financiers</td><td class="debit">500 000</td><td></td><tr>
                                                    <tr><td class="credit">521 – Banque</td><td></td><td class="credit">2 500 000</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mt-2">
                                        <i class="bi bi-info-circle"></i> <strong>Principe :</strong> Le SYSCOHADA privilégie la réalité économique : le bien est inscrit à l'actif même s'il n'appartient pas juridiquement à l'entreprise.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 3 : INVENTAIRE PERMANENT ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-box-seam"></i> 3. Inventaire permanent – Gestion des stocks</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Sortie de stock pour vente</div>
                                <div class="card-body">
                                    <p>Coût d'achat des marchandises sorties : 1 000 000 FCFA</p>
                                    <table class="table table-bordered table-t w-50 mx-auto">
                                        <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                        <tbody>
                                            <tr><td class="debit">603 – Variation des stocks</td><td class="debit">1 000 000</td><td></td></tr>
                                            <tr><td class="credit">31 – Stocks de marchandises</td><td></td><td class="credit">1 000 000</td></tr>
                                        </tbody>
                                    </table>
                                    <div class="alert alert-info mt-2">
                                        <i class="bi bi-info-circle"></i> <strong>Principe :</strong> L'inventaire permanent (obligatoire sous SYSCOHADA) permet de connaître le stock théorique en temps réel. Le compte 603 sert d'ajustement pour déterminer le coût des marchandises vendues.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 4 : COMPTABILITÉ ANALYTIQUE (CLASSE 9) ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-pie-chart"></i> 4. Comptabilité analytique (Classe 9) – Piloter par produit/service</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card ecriture-card">
                                <div class="card-header bg-light fw-bold">📌 Cas : Garage auto – Rentabilité de l'atelier "Réparation moteur"</div>
                                <div class="card-body">
                                    <p>Pièces consommées : 200 000 FCFA | Main-d'œuvre directe : 100 000 FCFA | Prix facturé client : 450 000 FCFA</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Transfert de charges vers le coût de revient</h6>
                                            <table class="table table-bordered table-t w-100">
                                                <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                                <tbody>
                                                    <tr><td class="debit">92 – Coût de revient (analytique)</td><td class="debit">300 000</td><td></td></tr>
                                                    <tr><td class="credit">90 – Charges par nature (comptabilité générale)</td><td></td><td class="credit">300 000</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Analyse du résultat analytique</h6>
                                            <table class="table table-bordered table-t w-100">
                                                <thead class="table-dark"><tr><th>Compte T</th><th>Débit</th><th>Crédit</th></tr></thead>
                                                <tbody>
                                                    <tr><td class="debit">94 – Produits analytiques (prix facturé)</td><td class="debit">450 000</td><td></td></tr>
                                                    <tr><td class="credit">92 – Coût de revient</td><td></td><td class="credit">300 000</td></tr>
                                                    <tr><td class="credit">95 – Résultat analytique (bénéfice)</td><td></td><td class="credit">150 000</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="alert alert-success mt-2">
                                        <strong>📊 Résultat analytique : 150 000 FCFA → service rentable !</strong>
                                    </div>
                                    <div class="alert alert-info mt-2">
                                        <i class="bi bi-info-circle"></i> <strong>Principe :</strong> La comptabilité analytique (classe 9) n'est pas obligatoire mais est indispensable pour le pilotage. Elle permet de détecter les "fuites" et d'aider à la décision (augmentation des prix, abandon de produit).
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 5 : SYNTHÈSE DES PROBLÉMATIQUES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-table"></i> 5. Synthèse : Problématiques UEMOA vs Solutions SYSCOHADA</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Problématique constatée</th><th>Réponse SYSCOHADA</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="problematique">Sous-évaluation des dettes fournisseurs en devises</td><td>Réévaluation systématique au cours de clôture (compte 674/774)</td></tr>
                                <tr><td class="problematique">Confusion location simple / Crédit-bail</td><td>Inscription à l'actif (compte 21) du bien en crédit-bail</td></tr>
                                <tr><td class="problematique">Gestion "à la louche" des stocks</td><td>Tenue obligatoire du compte 31/32 en inventaire permanent (compte 603)</td></tr>
                                <tr><td class="problematique">Non-conformité fiscale</td><td>Séparation stricte entre amortissements comptables et fiscaux</td></tr>
                                <tr><td class="problematique">Manque de visibilité par produit</td><td>Comptabilité analytique (classe 9) – centres d'analyse</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== CHAPITRE 6 : CONSEILS PRATIQUES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-tools"></i> 6. Conseils pour l'implémentation</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="bi bi-check-circle-fill text-success fs-1"></i>
                                    <h6>Fiabilisation</h6>
                                    <small>Avant toute écriture, validez la pièce justificative (facture, contrat, relevé)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="bi bi-diagram-3 text-primary fs-1"></i>
                                    <h6>Rigueur des comptes T</h6>
                                    <small>Pour chaque opération : Qu'est-ce que je possède (Actif) ? Qu'est-ce que je dois (Passif) ?</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="bi bi-graph-up text-info fs-1"></i>
                                    <h6>Audit interne</h6>
                                    <small>Rapprochement bancaire mensuel et état des tiers (401/411)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Note de mise en garde :</strong> La comptabilité analytique (Classe 9) est un outil de pilotage interne. Elle ne doit jamais "polluer" les états financiers légaux (Bilan / Compte de résultat) qui doivent rester basés sur la comptabilité générale (Classes 1 à 8).
                    </div>

                    <div class="alert alert-success mt-2">
                        <i class="bi bi-check-circle-fill"></i> <strong>Synthèse :</strong> Ce guide avancé complète le précédent en couvrant les opérations en devises, le crédit-bail, l'inventaire permanent et la comptabilité analytique – autant d'outils indispensables pour un pilotage financier précis et conforme au SYSCOHADA Révisé.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
