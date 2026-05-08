<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Explication - Comptabilisation des Salaires SYSCOHADA";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Comptabilisation des Salaires - Explication détaillée SYSCOHADA</h5>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <h6>📖 Schéma de comptabilisation SYSCOHADA :</h6>
                    <code>Débit 641 (Salaires) + Débit 651-653 (Charges sociales patronales) / Crédit 421 (Dettes personnel) + Crédit 431-433 (Organismes sociaux)</code>
                </div>

                <h5>🔍 DÉCOMPOSITION DU SCHÉMA</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark"><tr><th>Compte</th><th>Intitulé</th><th>Nature</th><th>Montant pour l'exemple</th><th>Explication</th></tr></thead>
                        <tbody>
                            <tr><td class="fw-bold">641</td><td>Rémunérations du personnel</td><td class="text-danger">DÉBIT</td><td>3.500.000 F</td><td>Salaire brut dû au salarié</td></tr>
                            <tr><td class="fw-bold">651</td><td>CNSS - Part employeur</td><td class="text-danger">DÉBIT</td><td>157.500 F</td><td>Cotisation CNSS (4.5%) à la charge de l'employeur</td></tr>
                            <tr><td class="fw-bold">652</td><td>IPRES - Part employeur</td><td class="text-danger">DÉBIT</td><td>280.000 F</td><td>Cotisation IPRES (8%) à la charge de l'employeur</td></tr>
                            <tr><td class="fw-bold">653</td><td>CSS - Part employeur</td><td class="text-danger">DÉBIT</td><td>245.000 F</td><td>Cotisation CSS (7%) à la charge de l'employeur</td></tr>
                            <tr><td class="fw-bold">421</td><td>Personnel - Rémunérations dues</td><td class="text-success">CRÉDIT</td><td>2.467.500 F</td><td>Net à payer au salarié (après déductions)</td></tr>
                            <tr><td class="fw-bold">431</td><td>CNSS - Part salariale</td><td class="text-success">CRÉDIT</td><td>157.500 F</td><td>Retenue CNSS sur salaire + part patronale</td></tr>
                            <tr><td class="fw-bold">432</td><td>IPRES - Part salariale</td><td class="text-success">CRÉDIT</td><td>600.000 F</td><td>Retenue IPRES (8% salarié + 8% employeur = 16%)</td></tr>
                            <tr><td class="fw-bold">433</td><td>CSS - Part salariale</td><td class="text-success">CRÉDIT</td><td>420.000 F</td><td>Retenue CSS (1% salarié + 7% employeur = 8%)</td></tr>
                            <tr><td class="fw-bold">4442</td><td>IRPP dû</td><td class="text-success">CRÉDIT</td><td>350.000 F</td><td>Impôt sur le revenu à reverser au fisc</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-success mt-3">
                    <strong>✅ Vérification :</strong> Total des débits = Total des crédits<br>
                    3.500.000 + 157.500 + 280.000 + 245.000 = <strong>4.182.500 F</strong><br>
                    2.467.500 + 157.500 + 600.000 + 420.000 + 350.000 = <strong>4.182.500 F</strong>
                </div>

                <div class="alert alert-warning mt-3">
                    <strong>⚖️ À retenir :</strong> Le coût total pour l'employeur = Salaires bruts + Charges patronales = 3.500.000 + 682.500 = 4.182.500 F
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
