<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - TVA Sénégal";
$page_icon = "percent";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

// Calcul TVA à partir des écritures réelles
$ca_ht = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$achats_ht = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();

$tva_collectee = $ca_ht * 0.18;  // Taux normal Sénégal
$tva_deductible = $achats_ht * 0.18;
$tva_a_payer = $tva_collectee - $tva_deductible;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5><i class="bi bi-percent"></i> Module 4 : La TVA au Sénégal (18%)</h5>
                <small>Taxe sur la Valeur Ajoutée - Régime réel normal</small>
            </div>
            <div class="card-body">
                
                <!-- Principes TVA -->
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 PRINCIPE DE LA TVA :</strong>
                    <p class="mt-2">La TVA est une taxe sur la consommation collectée par l'entreprise pour le compte de l'État.</p>
                    <ul>
                        <li><strong>TVA COLLECTÉE</strong> : Sur vos ventes (ce que vos clients vous paient)</li>
                        <li><strong>TVA DÉDUCTIBLE</strong> : Sur vos achats (ce que vous payez à vos fournisseurs)</li>
                        <li><strong>TVA À PAYER</strong> : Collectée - Déductible (à reverser au fisc)</li>
                    </ul>
                </div>
                
                <!-- Méthode de calcul -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-danger text-white">
                                <h6><i class="bi bi-calculator"></i> Calcul de la TVA Collectée</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Formule :</strong></p>
                                <code class="bg-white p-2 d-block mb-2">TVA Collectée = Montant HT × Taux (18%)</code>
                                <p><strong>Cas pratique :</strong> Vente de prestations 1.000.000 F HT</p>
                                <div class="alert alert-danger">
                                    TVA COLLECTÉE = 1.000.000 × 18% = 180.000 F<br>
                                    Total TTC = 1.180.000 F
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">
                                <h6><i class="bi bi-calculator"></i> Calcul de la TVA Déductible</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Formule :</strong></p>
                                <code class="bg-white p-2 d-block mb-2">TVA Déductible = Montant HT des achats × 18%</code>
                                <p><strong>Cas pratique :</strong> Achat de fournitures 300.000 F HT</p>
                                <div class="alert alert-success">
                                    TVA DÉDUCTIBLE = 300.000 × 18% = 54.000 F<br>
                                    Total TTC = 354.000 F
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Écritures comptables TVA -->
                <h5 class="mt-4">📝 ÉCRITURES COMPTABLES AVEC TVA</h5>
                
                <!-- Cas 1 : Vente avec TVA -->
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <strong>Cas n°1 : Facture client avec TVA</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong> Facture client 1.000.000 F HT + TVA 18% (180.000 F) = 1.180.000 F TTC</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-danger">
                                    <strong>📝 DÉBIT :</strong><br>
                                    <code>411 - Clients ............................. 1.180.000 F</code>
                                </div>
                                <div class="alert alert-success">
                                    <strong>📝 CRÉDIT :</strong><br>
                                    <code>703 - Prestations de services ........... 1.000.000 F</code><br>
                                    <code>4451 - TVA collectée (18%) ................ 180.000 F</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cas 2 : Achat avec TVA -->
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <strong>Cas n°2 : Facture fournisseur avec TVA</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong> Achat marchandises 400.000 F HT + TVA 18% (72.000 F) = 472.000 F TTC</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-danger">
                                    <strong>📝 DÉBIT :</strong><br>
                                    <code>601 - Achats de marchandises ............. 400.000 F</code><br>
                                    <code>4454 - TVA déductible (18%) ................ 72.000 F</code>
                                </div>
                                <div class="alert alert-success">
                                    <strong>📝 CRÉDIT :</strong><br>
                                    <code>401 - Fournisseurs ....................... 472.000 F</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Déclaration TVA -->
                <h5 class="mt-4">📊 DÉCLARATION TVA MENSUELLE</h5>
                <div class="card bg-light">
                    <div class="card-body">
                        <h6>Calcul à partir de vos opérations réelles :</h6>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6>TVA COLLECTÉE</h6>
                                        <h3><?= number_format($tva_collectee, 0, ',', ' ') ?> F</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6>TVA DÉDUCTIBLE</h6>
                                        <h3><?= number_format($tva_deductible, 0, ',', ' ') ?> F</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card <?= $tva_a_payer > 0 ? 'bg-warning' : 'bg-info' ?> text-white">
                                    <div class="card-body">
                                        <h6>TVA À PAYER / CRÉDIT</h6>
                                        <h3><?= number_format(abs($tva_a_payer), 0, ',', ' ') ?> F</h3>
                                        <small><?= $tva_a_payer > 0 ? 'À payer à la DGID' : 'Crédit à reporter' ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-secondary mt-3">
                            <i class="bi bi-calendar"></i>
                            <strong>Échéances TVA Sénégal :</strong><br>
                            - Déclaration mensuelle (avant le 15 du mois suivant)<br>
                            - Paiement simultané par virement ou chèque<br>
                            - Télédéclaration obligatoire via e-TVA
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../tva_module.php" class="btn btn-primary">Module complet TVA →</a>
                    <a href="sig.php" class="btn btn-success">Module suivant : SIG →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
