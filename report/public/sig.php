<?php
// sig.php
$page_title = "SIG - Soldes Intermédiaires de Gestion";

/*
  Chemins : ce fichier est prévu pour /var/www/report/public/sig.php
  Ajuste les require_once si ton layout/config est ailleurs
*/
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// normaliser $pdo (compatible plusieurs styles database.php)
if (function_exists('getConnection')) {
    $pdo = getConnection();
} elseif (isset($conn) && $conn instanceof PDO) {
    $pdo = $conn;
} elseif (isset($db) && $db instanceof PDO) {
    $pdo = $db;
} else {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=synthesepro_db;charset=utf8mb4', 'root', '123', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die("Erreur DB fallback : " . $e->getMessage());
    }
}

// include layout (suppose layout.php is dans /public)
require_once __DIR__ . '/layout.php';

// Helper : calcule net pour une classe (net positif pour produit, positif pour charges)
// méthode : sum credits for accounts of that class - sum debits for accounts of that class
function sum_for_class(PDO $pdo, array $classes, $year = null) {
    $placeholders = implode(',', array_fill(0, count($classes), '?'));
    $params = $classes;
    $yearFilter = "";
    if ($year) {
        $yearFilter = " AND YEAR(e.date_operation) = ? ";
        $params[] = $year;
    }

    // sum credited amounts on those accounts
    $sqlCred = "SELECT COALESCE(SUM(e.montant),0) FROM ECRITURES_COMPTABLES e
                JOIN PLAN_COMPTABLE_UEMOA p ON e.compte_credite_id = p.compte_id
                WHERE p.classe IN ($placeholders) $yearFilter";
    $stmt = $pdo->prepare($sqlCred);
    $stmt->execute($params);
    $cred = (float)$stmt->fetchColumn();

    // sum debited amounts on those accounts
    $sqlDeb = "SELECT COALESCE(SUM(e.montant),0) FROM ECRITURES_COMPTABLES e
                JOIN PLAN_COMPTABLE_UEMOA p ON e.compte_debite_id = p.compte_id
                WHERE p.classe IN ($placeholders) $yearFilter";
    $stmt = $pdo->prepare($sqlDeb);
    // params for debit query: same classes and year
    $stmt->execute($params);
    $deb = (float)$stmt->fetchColumn();

    return $cred - $deb; // net (positive means net credit for these classes)
}

// traitement formulaire / actions
$errors = [];
$messages = [];
$selectedYear = date('Y'); // default
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['year'])) $selectedYear = intval($_POST['year']);

    if (!empty($_POST['action']) && $_POST['action'] === 'export_csv') {
        // compute then export CSV
        // we reuse the computation further below; here just set flag
        $exportCsv = true;
    } else {
        $exportCsv = false;
    }

    if (!empty($_POST['action']) && $_POST['action'] === 'save_sig') {
        // compute and save to sig_results table
        $saveRequested = true;
    } else {
        $saveRequested = false;
    }
} else {
    $exportCsv = false;
    $saveRequested = false;
}

// ==== Calculs SIG ====
// NOTE : Hypothèses générales (modifiable) :
// - CA : comptes de classe 7 (revenus) => net = credits - debits
// - Charges : comptes de classe 6 => net = debits - credits (we compute net as credits-debits then invert for charges positive)
// - On simplifie : EBE = CA - Charges (approximation si tu n'avez pas ventilation achat/production indépendants)

$year = $selectedYear;

// CA (classe 7)
$ca_net = sum_for_class($pdo, [7], $year); // positive if credits > debits

// Charges (classe 6) : compute net as credits-debits then invert to get positive expense
$charges_net = - sum_for_class($pdo, [6], $year); // if sum_for_class returns negative, invert to positive

// Marge brute approximée : CA - Charges d'exploitation (ici charges_net)
$marge_brute = $ca_net - $charges_net;

// EBE approximé : marge_brute (no other adjustments) - ici we keep same
$ebe = $marge_brute;

// Résultat net approximé : CA - Charges
$resultat_net = $ca_net - $charges_net;

// total produits / charges (for display)
$total_produits = $ca_net; // class 7
$total_charges = $charges_net; // class 6

// prepare data for table
$sig = [
    'exercice' => $year,
    'ca' => $ca_net,
    'charges' => $charges_net,
    'marge_brute' => $marge_brute,
    'ebe' => $ebe,
    'resultat_net' => $resultat_net,
    'total_produits' => $total_produits,
    'total_charges' => $total_charges
];

// save optionally
if ($saveRequested) {
    $stmt = $pdo->prepare("INSERT INTO sig_results (exercice, ca, charges, marge_brute, ebe, resultat_net, date_calc) VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE)");
    $stmt->execute([$sig['exercice'], $sig['ca'], $sig['charges'], $sig['marge_brute'], $sig['ebe'], $sig['resultat_net']]);
    $messages[] = "<div class='alert alert-success'>SIG enregistré pour l'exercice {$sig['exercice']}.</div>";
}

// export CSV if requested
if (isset($exportCsv) && $exportCsv) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sig_' . $year . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Exercice','CA','Charges','Marge brute','EBE','Résultat net','Total produits','Total charges']);
    fputcsv($out, [$sig['exercice'],$sig['ca'],$sig['charges'],$sig['marge_brute'],$sig['ebe'],$sig['resultat_net'],$sig['total_produits'],$sig['total_charges']]);
    fclose($out);
    exit;
}

// Optionnel : récupérer historique des SIG sauvegardés
$history = $pdo->query("SELECT * FROM sig_results ORDER BY exercice DESC, id DESC LIMIT 100")->fetchAll();

?>
<div class="container-fluid">

    <?php foreach ($messages as $m) echo $m; ?>
    <?php foreach ($errors as $e) echo "<div class='alert alert-danger'>{$e}</div>"; ?>

    <div class="card p-4 shadow-sm mb-4">
        <h5 class="mb-3">SIG - Soldes Intermédiaires de Gestion</h5>
        <form method="post" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Année (Exercice)</label>
                <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($selectedYear) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Actions</label>
                <div>
                    <button type="submit" name="action" value="compute" class="btn btn-primary me-2">Calculer</button>
                    <button type="submit" name="action" value="export_csv" class="btn btn-outline-secondary me-2">Exporter CSV</button>
                    <button type="submit" name="action" value="save_sig" class="btn btn-success">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Résultats SIG -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card p-3 shadow-sm">
                <h6>Tableau SIG - Exercice <?= htmlspecialchars($sig['exercice']) ?></h6>
                <table class="table table-bordered mt-3">
                    <thead class="table-dark">
                        <tr><th>Poste</th><th>Montant</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Chiffre d'affaires (Classe 7)</td><td class="text-end"><?= number_format($sig['ca'],2,',',' ') ?> FCFA</td></tr>
                        <tr><td>Charges (Classe 6)</td><td class="text-end"><?= number_format($sig['charges'],2,',',' ') ?> FCFA</td></tr>
                        <tr><td>Marge brute (CA - Charges)</td><td class="text-end"><?= number_format($sig['marge_brute'],2,',',' ') ?> FCFA</td></tr>
                        <tr><td>EBE (approx.)</td><td class="text-end"><?= number_format($sig['ebe'],2,',',' ') ?> FCFA</td></tr>
                        <tr><td>Résultat net (approx.)</td><td class="text-end"><?= number_format($sig['resultat_net'],2,',',' ') ?> FCFA</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card mt-3 p-3 shadow-sm">
                <h6>Graphique : CA vs Charges vs Résultat</h6>
                <canvas id="sigChart" height="120"></canvas>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6>Résumé / KPI</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">CA <span><?= number_format($sig['ca'],2,',',' ') ?></span></li>
                    <li class="list-group-item d-flex justify-content-between">Charges <span><?= number_format($sig['charges'],2,',',' ') ?></span></li>
                    <li class="list-group-item d-flex justify-content-between">Marge brute <span><?= number_format($sig['marge_brute'],2,',',' ') ?></span></li>
                    <li class="list-group-item d-flex justify-content-between">EBE <span><?= number_format($sig['ebe'],2,',',' ') ?></span></li>
                    <li class="list-group-item d-flex justify-content-between">Résultat net <span><?= number_format($sig['resultat_net'],2,',',' ') ?></span></li>
                </ul>
            </div>

            <div class="card mt-3 p-3 shadow-sm">
                <h6>Historique SIG (enregistré)</h6>
                <table class="table table-sm mt-2">
                    <thead><tr><th>Ex.</th><th>CA</th><th>Résultat</th></tr></thead>
                    <tbody>
                        <?php if (empty($history)) echo '<tr><td colspan="3" class="text-center">Aucun</td></tr>'; ?>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= $h['exercice'] ?></td>
                                <td class="text-end"><?= number_format($h['ca'],2,',',' ') ?></td>
                                <td class="text-end"><?= number_format($h['resultat_net'],2,',',' ') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('sigChart').getContext('2d');
const sigChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['CA','Charges','Résultat Net'],
        datasets: [{
            label: 'Montant (FCFA)',
            data: [<?= json_encode([$sig['ca'],$sig['charges'],$sig['resultat_net']]) ?>],
            backgroundColor: ['#2b8bf2','#ff6b6b','#6bcf60']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});
</script>

