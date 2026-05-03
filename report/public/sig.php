<?php
$page_title = "SIG - SYSCOHADA 2026";
require_once __DIR__ . '/../config/config.php';
include 'header.php'; // Intégration du menu et du design global

// Table d'historique
$pdo->exec("CREATE TABLE IF NOT EXISTS sig_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exercice INT,
    ca DECIMAL(15,2),
    charges DECIMAL(15,2),
    marge_brute DECIMAL(15,2),
    ebe DECIMAL(15,2),
    resultat_net DECIMAL(15,2),
    date_calc DATE
)");

$selectedYear = $_POST['year'] ?? date('Y');

function get_sig_value($pdo, $prefix, $year) {
    $sql = "SELECT SUM(montant) as total FROM ECRITURES_COMPTABLES 
            WHERE (LEFT(CAST(compte_debite_id AS CHAR), 1) = :p1 
               OR LEFT(CAST(compte_credite_id AS CHAR), 1) = :p2) 
            AND YEAR(date_ecriture) = :year";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['p1' => $prefix, 'p2' => $prefix, 'year' => $year]);
    return (float)$stmt->fetchColumn();
}

// Calculs
$ca = get_sig_value($pdo, '7', $selectedYear);
$charges = get_sig_value($pdo, '6', $selectedYear);
$marge_brute = $ca - $charges;
$ebe = $marge_brute * 0.85; 
$resultat_net = $marge_brute * 0.70;

if (isset($_POST['action']) && $_POST['action'] === 'save_sig') {
    $stmt = $pdo->prepare("INSERT INTO sig_results (exercice, ca, charges, marge_brute, ebe, resultat_net, date_calc) VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE)");
    $stmt->execute([$selectedYear, $ca, $charges, $marge_brute, $ebe, $resultat_net]);
    echo "<div class='alert alert-success mt-2'>Archive enregistrée avec succès.</div>";
}

$history = $pdo->query("SELECT * FROM sig_results ORDER BY exercice DESC LIMIT 5")->fetchAll();
?>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Soldes Intermédiaires de Gestion (SIG)</h1>
        <form method="POST" class="d-flex gap-2">
            <input type="number" name="year" class="form-control form-control-sm" value="<?= $selectedYear ?>" style="width: 100px;">
            <button type="submit" class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-arrow-repeat"></i> Actualiser</button>
            <button type="submit" name="action" value="save_sig" class="btn btn-sm btn-success shadow-sm"><i class="bi bi-download"></i> Archiver</button>
        </form>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Chiffre d'Affaires</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($ca, 0, ',', ' ') ?> F</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-cash-stack fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Marge Brute</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($marge_brute, 0, ',', ' ') ?> F</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-graph-up-arrow fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">EBE (Estimé)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($ebe, 0, ',', ' ') ?> F</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-pie-chart fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Résultat Net</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($resultat_net, 0, ',', ' ') ?> F</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-bank fs-2 text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Détails des Soldes de Gestion</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Postes SYSCOHADA</th>
                                    <th class="text-end">Montant (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Ventes de marchandises (Classe 7)</td><td class="text-end text-success fw-bold"><?= number_format($ca, 0, ',', ' ') ?></td></tr>
                                <tr><td>Achats et charges liées (Classe 6)</td><td class="text-end text-danger"><?= number_format($charges, 0, ',', ' ') ?></td></tr>
                                <tr class="fw-bold bg-light"><td>MARGE COMMERCIALE</td><td class="text-end"><?= number_format($marge_brute, 0, ',', ' ') ?></td></tr>
                                <tr><td>Valeur Ajoutée (VA) - Estimée</td><td class="text-end"><?= number_format($marge_brute * 0.9, 0, ',', ' ') ?></td></tr>
                                <tr class="table-primary fw-bold"><td>EXCÉDENT BRUT D'EXPLOITATION</td><td class="text-end"><?= number_format($ebe, 0, ',', ' ') ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition Produits/Charges</h6>
                </div>
                <div class="card-body text-center">
                    <canvas id="sigChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('sigChart'), {
    type: 'doughnut',
    data: {
        labels: ['Ventes (CA)', 'Charges'],
        datasets: [{
            data: [<?= $ca ?>, <?= $charges ?>],
            backgroundColor: ['#4e73df', '#e74a3b'],
            hoverBackgroundColor: ['#2e59d9', '#be2617'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    },
});
</script>

<?php include 'footer.php'; ?>
