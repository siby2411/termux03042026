<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Formation - Facturation";
$page_icon = "file-invoice";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-file-invoice"></i> Formation : Facturation</h5>
                <small>Émission et gestion des factures clients/fournisseurs</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>📚 À savoir :</strong> Une facture génère automatiquement une écriture comptable (Débit client / Crédit produit).
                </div>
                
                <!-- Types de factures -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-cart-plus fs-1 text-success"></i>
                                <h6>Facture de vente</h6>
                                <small>Client → Produits/services</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-cart-dash fs-1 text-danger"></i>
                                <h6>Avoir</h6>
                                <small>Réduction, retour, remise</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-truck fs-1 text-primary"></i>
                                <h6>Facture d'achat</h6>
                                <small>Fournisseur → Matières/services</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Calcul automatique -->
                <div class="card bg-light mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-calculator"></i> Calculateur TVA intégré
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Montant HT (FCFA)</label>
                                <input type="number" id="ht" class="form-control" placeholder="Ex: 1000000" oninput="calculTVA()">
                            </div>
                            <div class="col-md-3">
                                <label>Taux TVA</label>
                                <select id="taux" class="form-select" onchange="calculTVA()">
                                    <option value="0">0% (Exonéré)</option>
                                    <option value="5">5% (Taux réduit)</option>
                                    <option value="10">10% (Taux intermédiaire)</option>
                                    <option value="18" selected>18% (Taux normal Sénégal)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Montant TTC (FCFA)</label>
                                <input type="text" id="ttc" class="form-control bg-white" readonly>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label>Montant TVA</label>
                                <input type="text" id="tva" class="form-control bg-white" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cas pratique -->
                <div class="card bg-success text-white">
                    <div class="card-header">📋 CAS PRATIQUE</div>
                    <div class="card-body">
                        <p><strong>Créez une facture de vente pour le client CLI001 :</strong></p>
                        <ul>
                            <li>Prestation de conseil : 750.000 F HT</li>
                            <li>TVA 18% : 135.000 F</li>
                            <li>Total TTC : 885.000 F</li>
                        </ul>
                        <button class="btn btn-light" onclick="window.open('../facturation.php', '_blank')">🧾 Générer la facture →</button>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="../facturation.php" class="btn btn-success">Accéder au module Facturation</a>
                    <a href="tiers.php" class="btn btn-outline-primary">← Module précédent</a>
                    <a href="engagements_hors_bilan.php" class="btn btn-outline-primary">Module suivant →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculTVA() {
    let ht = parseFloat(document.getElementById('ht').value) || 0;
    let taux = parseFloat(document.getElementById('taux').value) / 100;
    let tva = ht * taux;
    let ttc = ht + tva;
    
    document.getElementById('tva').value = new Intl.NumberFormat().format(tva) + ' F';
    document.getElementById('ttc').value = new Intl.NumberFormat().format(ttc) + ' F';
}
</script>

<?php include '../inc_footer.php'; ?>
