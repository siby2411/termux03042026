<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Cas pratiques - Achats, Ventes, TVA";
$page_icon = "cart";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-cart"></i> Cas pratiques - Opérations commerciales SYSCOHADA</h5>
                <small>Achats, ventes, avoirs, TVA - Conformité OHADA</small>
            </div>
            <div class="card-body">
                
                <!-- Cas 1 : Achat de marchandises -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">📦 Cas n°1 : Achat de marchandises au comptant</div>
                    <div class="card-body">
                        <p><strong>Scénario</strong> : GHI SARL achète 500.000 FCFA de marchandises, payé par chèque bancaire.</p>
                        <div class="row">
                            <div class="col-md-6"><div class="alert alert-danger"><strong>DÉBIT</strong><br><code>601 - Achats de marchandises ........ 500.000 F</code></div></div>
                            <div class="col-md-6"><div class="alert alert-success"><strong>CRÉDIT</strong><br><code>521 - Banque ...................... 500.000 F</code></div></div>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="chargerEcriture(601,521,500000,'Achat marchandises')">Saisir cette écriture</button>
                    </div>
                </div>
                
                <!-- Cas 2 : Facture d'achat avec TVA -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">📄 Cas n°2 : Facture d'achat avec TVA 18%</div>
                    <div class="card-body">
                        <p><strong>Scénario</strong> : Achat 400.000 F HT + TVA 72.000 F = 472.000 F TTC à crédit</p>
                        <div class="row">
                            <div class="col-md-6"><div class="alert alert-danger"><strong>DÉBIT</strong><br><code>601 - Achats ................. 400.000 F</code><br><code>4454 - TVA déductible ........ 72.000 F</code></div></div>
                            <div class="col-md-6"><div class="alert alert-success"><strong>CRÉDIT</strong><br><code>401 - Fournisseur ............ 472.000 F</code></div></div>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="chargerEcriture(601,401,400000,'Achat TVA')">Saisir</button>
                    </div>
                </div>
                
                <!-- Cas 3 : Vente de biens -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">💰 Cas n°3 : Vente de biens (entreprise industrielle)</div>
                    <div class="card-body">
                        <p><strong>Scénario</strong> : Vente de meubles 1.000.000 F HT au comptant</p>
                        <div class="row">
                            <div class="col-md-6"><div class="alert alert-danger"><strong>DÉBIT</strong><br><code>521 - Banque ............... 1.180.000 F</code></div></div>
                            <div class="col-md-6"><div class="alert alert-success"><strong>CRÉDIT</strong><br><code>701 - Ventes ............... 1.000.000 F</code><br><code>4451 - TVA collectée ........ 180.000 F</code></div></div>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="chargerEcriture(521,701,1000000,'Vente meubles')">Saisir</button>
                    </div>
                </div>
                
                <!-- Cas 4 : Avoir commercial -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">🔄 Cas n°4 : Avoir commercial (retour marchandise)</div>
                    <div class="card-body">
                        <p><strong>Scénario</strong> : Retour de marchandises 200.000 F HT</p>
                        <div class="row">
                            <div class="col-md-6"><div class="alert alert-danger"><strong>DÉBIT</strong><br><code>706 - Rabais, remises ...... 200.000 F</code></div></div>
                            <div class="col-md-6"><div class="alert alert-success"><strong>CRÉDIT</strong><br><code>411 - Client ............... 236.000 F</code></div></div>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="chargerEcriture(706,411,200000,'Avoir commercial')">Saisir</button>
                    </div>
                </div>
                
                <!-- Cas 5 : Reconstitution facture -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">📑 Cas n°5 : Reconstitution d'une facture</div>
                    <div class="card-body">
                        <p><strong>Données facture :</strong> Prestations services 750.000 F HT, TVA 18%, TTC = 885.000 F</p>
                        <div class="row">
                            <div class="col-md-6"><div class="alert alert-danger"><strong>DÉBIT</strong><br><code>411 - Client ............... 885.000 F</code></div></div>
                            <div class="col-md-6"><div class="alert alert-success"><strong>CRÉDIT</strong><br><code>703 - Prestations .......... 750.000 F</code><br><code>4451 - TVA collectée ........ 135.000 F</code></div></div>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="chargerEcriture(411,703,750000,'Facture prestation')">Saisir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function chargerEcriture(deb, cred, mt, lib) {
    document.querySelector('select[name="debite"]').value = deb;
    document.querySelector('select[name="credite"]').value = cred;
    document.querySelector('input[name="montant"]').value = mt;
    document.querySelector('input[name="libelle"]').value = lib;
    document.querySelector('form').scrollIntoView();
}
</script>

<!-- Redirection vers écriture contrôlée -->
<div class="row mt-4">
    <div class="col-md-12 text-center">
        <a href="ecriture_controlee.php" class="btn btn-omega">
            <i class="bi bi-pencil-square"></i> Accéder à la saisie d'écriture contrôlée
        </a>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
