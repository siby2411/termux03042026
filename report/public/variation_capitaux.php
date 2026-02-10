<?php
// variation_capitaux.php
$page_title = "Variation Capitaux Propres";

// inclure config et layout (chemins relatifs depuis public/)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// normaliser $pdo (compatible avec différents database.php)
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

// include layout (ajuste le chemin si ton layout est dans public/)
require_once __DIR__ . '/layout.php';

// POST : insertion
$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'insert_variation') {
        $exercice = (int)($_POST['exercice'] ?? 0);
        $capital_initial = floatval($_POST['capital_initial'] ?? 0);
        $resultat_exercice = floatval($_POST['resultat_exercice'] ?? 0);
        $dividendes = floatval($_POST['dividendes'] ?? 0);
        $autres = floatval($_POST['autres_ajustements'] ?? 0);
        $comment = trim($_POST['commentaire'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO variation_capitaux (exercice, capital_initial, resultat_exercice, dividendes, autres_ajustements, commentaire) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$exercice, $capital_initial, $resultat_exercice, $dividendes, $autres, $comment]);
        $messages[] = "<div class='alert alert-success'>Variation enregistrée.</div>";
    }

    // Export CSV
    if ($_POST['action'] === 'export_csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=variation_capitaux.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','exercice','date_enregistrement','capital_initial','resultat_exercice','dividendes','autres_ajustements','capital_final','commentaire']);
        $rows = $pdo->query("SELECT * FROM variation_capitaux ORDER BY exercice DESC, id DESC")->fetchAll();
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],$r['exercice'],$r['date_enregistrement'],
                $r['capital_initial'],$r['resultat_exercice'],$r['dividendes'],$r['autres_ajustements'],$r['capital_final'],$r['commentaire']
            ]);
        }
        fclose($out);
        exit;
    }
}

// Récupérer données pour affichage et graphique
$data = $pdo->query("SELECT exercice, SUM(capital_final) AS total_capital FROM variation_capitaux GROUP BY exercice ORDER BY exercice ASC")->fetchAll();
$rows = $pdo->query("SELECT * FROM variation_capitaux ORDER BY exercice DESC, id DESC")->fetchAll();

?>
<div class="container-fluid">
    <?php foreach ($messages as $m) echo $m; ?>

    <div class="card p-4 mb-4 shadow-sm">
        <h5 class="mb-3">Ajouter / Calculer Variation des Capitaux Propres</h5>
        <form method="post" class="row g-3">
            <input type="hidden" name="action" value="insert_variation">
            <div class="col-md-2">
                <label class="form-label">Exercice</label>
                <input type="number" name="exercice" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Capital initial</label>
                <input type="number" step="0.01" name="capital_initial" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Résultat exercice</label>
                <input type="number" step="0.01" name="resultat_exercice" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dividendes</label>
                <input type="number" step="0.01" name="dividendes" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Autres ajustements</label>
                <input type="number" step="0.01" name="autres_ajustements" class="form-control">
            </div>
            <div class="col-md-12">
                <label class="form-label">Commentaire</label>
                <textarea name="commentaire" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-success" type="submit">Enregistrer</button>
                <button class="btn btn-outline-secondary" type="submit" name="action" value="export_csv" formnovalidate>Exporter CSV</button>
            </div>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card p-3 shadow-sm">
                <h6>Évolution du capital par exercice</h6>
                <canvas id="capChart" height="140"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6>Résumé</h6>
                <ul class="list-group list-group-flush">
                    <?php
                    $tot = 0;
                    foreach($data as $d) {
                        $tot += floatval($d['total_capital']);
                        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>{$d['exercice']}<span>".number_format($d['total_capital'],2,',',' ')." FCFA</span></li>";
                    }
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">Total<span><?= number_format($tot,2,',',' ') ?> FCFA</span></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card p-3 shadow-sm">
        <h6>Historique des variations</h6>
        <table class="table table-striped mt-3">
            <thead class="table-dark">
                <tr><th>Exercice</th><th>Date</th><th>Capital initial</th><th>Résultat</th><th>Dividendes</th><th>Autres</th><th>Capital final</th><th>Commentaire</th></tr>
            </thead>
            <tbody>
                <?php foreach($rows as $r): ?>
                <tr>
                    <td><?= $r['exercice'] ?></td>
                    <td><?= $r['date_enregistrement'] ?></td>
                    <td><?= number_format($r['capital_initial'],2,',',' ') ?></td>
                    <td><?= number_format($r['resultat_exercice'],2,',',' ') ?></td>
                    <td><?= number_format($r['dividendes'],2,',',' ') ?></td>
                    <td><?= number_format($r['autres_ajustements'],2,',',' ') ?></td>
                    <td><?= number_format($r['capital_final'],2,',',' ') ?></td>
                    <td><?= htmlspecialchars($r['commentaire']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)) echo '<tr><td colspan="8" class="text-center">Aucune donnée.</td></tr>'; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const capCtx = document.getElementById('capChart').getContext('2d');
const labels = <?= json_encode(array_column($data,'exercice')) ?>;
const values = <?= json_encode(array_map(function($d){ return floatval($d['total_capital']); }, $data)); ?>;

new Chart(capCtx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Capital final',
            data: values,
            fill: true,
            tension: 0.3,
            borderWidth: 2,
            pointRadius: 3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});
</script>

