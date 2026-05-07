<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Formation - Déclarations fiscales";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5><i class="bi bi-file-text"></i> Formation : Déclarations fiscales Sénégal</h5>
                <small>TVA, IR, IS, CSS, IPRES - Échéances et calculs</small>
            </div>
            <div class="card-body">
                
                <!-- Calendrier fiscal -->
                <div class="alert alert-info">
                    <i class="bi bi-calendar"></i>
                    <strong>📅 Calendrier fiscal Sénégal :</strong>
                </div>
                
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Taxe</th><th>Périodicité</th><th>Échéance</th><th>Taux</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>TVA</td><td>Mensuelle</td><td>15 du mois suivant</td><td>18%</td></tr>
                            <tr><td>IR (Impôt sur Revenus)</td><td>Trimestrielle</td><td>30 avril, 31 juillet, 31 octobre</td><td>20-40%</td></tr>
                            <tr><td>IS (Impôt Sociétés)</td><td>Annuelle</td><td>30 avril N+1</td><td>25%</td></tr>
                            <tr><td>CSS (Couverture Santé)</td><td>Mensuelle</td><td>15 du mois suivant</td><td>7%</td></tr>
                            <tr><td>IPRES (Retraite)</td><td>Mensuelle</td><td>15 du mois suivant</td><td>16%</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Calculateur TVA -->
                <div class="card bg-light">
                    <div class="card-header bg-success text-white">🧮 Calculateur TVA à payer</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>TVA collectée (ventes)</label>
                                <input type="number" id="collectee" class="form-control" placeholder="Montant TVA collectée">
                            </div>
                            <div class="col-md-6">
                                <label>TVA déductible (achats)</label>
                                <input type="number" id="deductible" class="form-control" placeholder="Montant TVA déductible">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button class="btn btn-primary" onclick="calculTVAAPayer()">Calculer TVA à payer</button>
                                <span id="resultatTVA" class="ms-3 fw-bold"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../declarations_fiscales.php" class="btn btn-warning">Accéder au module</a>
                    <a href="../didactiel/" class="btn btn-outline-secondary">← Retour didacticiel</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculTVAAPayer() {
    let collectee = parseFloat(document.getElementById('collectee').value) || 0;
    let deductible = parseFloat(document.getElementById('deductible').value) || 0;
    let aPayer = collectee - deductible;
    let result = document.getElementById('resultatTVA');
    
    if(aPayer > 0) {
        result.innerHTML = 'TVA à payer : ' + new Intl.NumberFormat().format(aPayer) + ' F';
        result.className = 'text-danger fw-bold ms-3';
    } else if(aPayer < 0) {
        result.innerHTML = 'Crédit de TVA : ' + new Intl.NumberFormat().format(Math.abs(aPayer)) + ' F à reporter';
        result.className = 'text-success fw-bold ms-3';
    } else {
        result.innerHTML = 'Aucune TVA à payer';
        result.className = 'text-secondary fw-bold ms-3';
    }
}
</script>

<?php include '../inc_footer.php'; ?>
